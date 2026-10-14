<?php

namespace App\Models;

use App\Support\Workflow;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'aso_number',
        'sjo_number',
        'date_filed',
        'bds_name',
        'bds_email',
        'client_name',
        'total_amount',
        'start_date',
        'end_date',
        'current_step',
        'status',
        'data',
        'sjo',
        'it',
        'checks',
        'user_id',
        'declined_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sjo' => 'array',
            'it' => 'array',
            'checks' => 'array',
            'date_filed' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'declined_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * User who filed this sales order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Timestamped action history logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class)->orderBy('created_at', 'asc');
    }

    /**
     * Auto-generate unique sequential ASO Number.
     */
    public static function generateNextAsoNumber(): string
    {
        $year = date('Y');
        $prefix = "ASO-{$year}-";

        $latest = static::where('aso_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest->aso_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Auto-generate unique sequential SJO Number.
     */
    public static function generateNextSjoNumber(): string
    {
        $year = date('Y');
        $prefix = "SJO-{$year}-";

        $latest = static::where('sjo_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest->sjo_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Step information from Workflow config.
     */
    public function getStepInfoAttribute(): array
    {
        $all = Workflow::steps();
        return $all[$this->current_step] ?? [
            'title' => ucfirst(str_replace('_', ' ', $this->current_step)),
            'stage' => 'Workflow',
            'roles' => [],
        ];
    }

    /**
     * Formatted Peso currency string.
     */
    public function getFormattedAmountAttribute(): string
    {
        return Workflow::formatMoney($this->total_amount);
    }

    /**
     * Determine if order is in terminal Complete state.
     */
    public function isComplete(): bool
    {
        return $this->status === Workflow::STATUS_COMPLETE;
    }

    /**
     * Determine if order is in terminal Declined state.
     */
    public function isDeclined(): bool
    {
        return $this->status === Workflow::STATUS_DECLINED;
    }

    /**
     * Determine if order is currently active.
     */
    public function isInProgress(): bool
    {
        return $this->status === Workflow::STATUS_IN_PROGRESS;
    }

    /**
     * Check if a given user can act on the current step.
     */
    public function canBeActedOnBy(?User $user): bool
    {
        if (!$user || !$this->isInProgress()) {
            return false;
        }

        return Workflow::canUserActOnStep($user, $this->current_step);
    }

    /**
     * Scope: Orders waiting on the user's specific roles.
     */
    public function scopeWaitingOnUser(Builder $query, User $user): Builder
    {
        if ($user->is_admin || $user->hasRole(['admin', 'it-admin'])) {
            return $query->where('status', Workflow::STATUS_IN_PROGRESS);
        }

        $userRoles = $user->roles->pluck('slug')->toArray();
        $matchingSteps = [];

        foreach (Workflow::steps() as $stepKey => $stepDef) {
            if (!empty($stepDef['roles']) && !empty(array_intersect($stepDef['roles'], $userRoles))) {
                $matchingSteps[] = $stepKey;
            }
        }

        if (empty($matchingSteps)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('status', Workflow::STATUS_IN_PROGRESS)
            ->whereIn('current_step', $matchingSteps);
    }

    /**
     * Scope: My queue / accessible orders.
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        // Admins and Accounting see all orders
        if ($user->is_admin || $user->hasRole(['admin', 'it-admin', 'accounting'])) {
            return $query;
        }

        $userRoles = $user->roles->pluck('slug')->toArray();
        $matchingSteps = [];

        foreach (Workflow::steps() as $stepKey => $stepDef) {
            if (!empty($stepDef['roles']) && !empty(array_intersect($stepDef['roles'], $userRoles))) {
                $matchingSteps[] = $stepKey;
            }
        }

        return $query->where(function (Builder $q) use ($user, $matchingSteps) {
            // Filed by user
            $q->where('user_id', $user->id)
                // Or waiting on user's role
                ->when(!empty($matchingSteps), function ($sub) use ($matchingSteps) {
                    $sub->orWhere(function ($stepQ) use ($matchingSteps) {
                        $stepQ->where('status', Workflow::STATUS_IN_PROGRESS)
                            ->whereIn('current_step', $matchingSteps);
                    });
                })
                // Or user acted on it previously in logs
                ->orWhereHas('logs', function ($logQ) use ($user) {
                    $logQ->where('user_id', $user->id);
                });
        });
    }

    /**
     * Monthly amortization schedule: spread total amount evenly across campaign months.
     */
    public function getAmortizationSchedule(): array
    {
        $amount = (float) $this->total_amount;
        if ($amount <= 0 || !$this->start_date || !$this->end_date) {
            return [];
        }

        $start = Carbon::parse($this->start_date)->startOfMonth();
        $end = Carbon::parse($this->end_date)->startOfMonth();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $period = CarbonPeriod::create($start, '1 month', $end);
        $totalMonths = max(1, count($period));
        $monthlyAmount = round($amount / $totalMonths, 2);

        $schedule = [];
        $runningBalance = $amount;

        foreach ($period as $index => $dt) {
            $isLast = ($index === $totalMonths - 1);
            $monthShare = $isLast ? $runningBalance : min($monthlyAmount, $runningBalance);
            $runningBalance = max(0, $runningBalance - $monthShare);

            $schedule[] = [
                'month_label' => $dt->format('F Y'),
                'amount' => $monthShare,
                'formatted_amount' => Workflow::formatMoney($monthShare),
                'remaining_balance' => $runningBalance,
                'formatted_remaining' => Workflow::formatMoney($runningBalance),
            ];
        }

        return $schedule;
    }

    /**
     * Accrual and payment milestones breakdown:
     * - 90% progress billing
     * - 10% retention held until campaign end
     */
    public function getMilestones(): array
    {
        $total = (float) $this->total_amount;
        $retentionRate = Workflow::RETENTION_PERCENTAGE; // 10%
        $retentionAmount = round($total * $retentionRate, 2);
        $progressBillingAmount = round($total - $retentionAmount, 2);

        return [
            'total' => $total,
            'formatted_total' => Workflow::formatMoney($total),
            'progress_rate' => (1 - $retentionRate) * 100, // 90%
            'progress_billing' => $progressBillingAmount,
            'formatted_progress_billing' => Workflow::formatMoney($progressBillingAmount),
            'retention_rate' => $retentionRate * 100, // 10%
            'retention_amount' => $retentionAmount,
            'formatted_retention_amount' => Workflow::formatMoney($retentionAmount),
            'retention_release_date' => $this->end_date ? Carbon::parse($this->end_date)->format('M d, Y') : 'Campaign Completion',
        ];
    }
}
