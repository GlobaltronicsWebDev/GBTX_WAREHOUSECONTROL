<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\User;
use App\Notifications\OrderStepNotification;
use App\Support\Workflow;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    /**
     * Dashboard & Order Queue.
     */
    public function index(Request $request): View
    {
        if (!Schema::hasTable('sales_orders')) {
            return view('admin.sales-orders.migration-needed');
        }

        $user = Auth::user();
        $tab = $request->query('tab', 'my_queue'); // 'my_queue' or 'all'
        $status = $request->query('status');
        $step = $request->query('step');
        $search = $request->query('search');

        // Base query depending on tab
        if ($tab === 'my_queue') {
            $query = SalesOrder::waitingOnUser($user)->with(['user', 'logs']);
        } else {
            $query = SalesOrder::accessibleBy($user)->with(['user', 'logs']);
        }

        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }

        if ($step) {
            $query->where('current_step', $step);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('aso_number', 'like', "%{$search}%")
                    ->orWhere('sjo_number', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('bds_name', 'like', "%{$search}%")
                    ->orWhere('bds_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Calculate summary metrics across all accessible orders
        $allAccessible = SalesOrder::accessibleBy($user);
        $totalOrders = (clone $allAccessible)->count();
        $inProgressCount = (clone $allAccessible)->where('status', Workflow::STATUS_IN_PROGRESS)->count();
        $completeCount = (clone $allAccessible)->where('status', Workflow::STATUS_COMPLETE)->count();
        $declinedCount = (clone $allAccessible)->where('status', Workflow::STATUS_DECLINED)->count();

        $approvedContractValue = (clone $allAccessible)->where('status', Workflow::STATUS_COMPLETE)->sum('total_amount');
        $valueAwaitingApproval = (clone $allAccessible)->where('status', Workflow::STATUS_IN_PROGRESS)->sum('total_amount');

        // My actionable queue count
        $myQueueCount = SalesOrder::waitingOnUser($user)->count();

        return view('admin.sales-orders.index', [
            'orders' => $orders,
            'tab' => $tab,
            'status' => $status,
            'step' => $step,
            'search' => $search,
            'totalOrders' => $totalOrders,
            'inProgressCount' => $inProgressCount,
            'completeCount' => $completeCount,
            'declinedCount' => $declinedCount,
            'approvedContractValue' => $approvedContractValue,
            'valueAwaitingApproval' => $valueAwaitingApproval,
            'myQueueCount' => $myQueueCount,
            'workflowSteps' => Workflow::steps(),
        ]);
    }

    /**
     * Show ASO Creation Form.
     */
    public function create(): View
    {
        if (!Schema::hasTable('sales_orders')) {
            return view('admin.sales-orders.migration-needed');
        }

        $this->authorize('create', SalesOrder::class);

        $defaultAsoNumber = SalesOrder::generateNextAsoNumber();
        $asoFields = Workflow::asoFields();

        return view('admin.sales-orders.create', [
            'defaultAsoNumber' => $defaultAsoNumber,
            'asoFields' => $asoFields,
        ]);
    }

    /**
     * Store newly filed ASO Sales Order.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        $validated = $request->validate([
            'aso_number' => ['required', 'string', 'max:50', 'unique:sales_orders,aso_number'],
            'date_filed' => ['required', 'date'],
            'bds_name' => ['required', 'string', 'max:150'],
            'bds_email' => ['required', 'email', 'max:150'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_details' => ['required', 'string'],
            'ads_details' => ['required', 'string'],
            'led_sites' => ['required', 'string'],
            'rate_per_spot' => ['required', 'numeric', 'min:0'],
            'spot_duration' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'advertising_material_type' => ['required', 'string'],
            'po_number' => ['required', 'string', 'max:100'],
            'ref_number' => ['nullable', 'string', 'max:100'],
            'billing_contact_person' => ['required', 'string', 'max:150'],
            'billing_contact_number' => ['required', 'string', 'max:100'],
            'billing_address' => ['required', 'string'],
            'tin' => ['required', 'string', 'max:50'],
            'business_style' => ['nullable', 'string', 'max:100'],
            'vat' => ['required', 'string'],
            'payment_terms' => ['required', 'string'],
            'total_project_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $user = Auth::user();

            $order = SalesOrder::create([
                'aso_number' => $validated['aso_number'],
                'date_filed' => $validated['date_filed'],
                'bds_name' => $validated['bds_name'],
                'bds_email' => $validated['bds_email'],
                'client_name' => $validated['client_name'],
                'total_amount' => $validated['total_project_amount'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'current_step' => Workflow::STEP_ASO_APPROVAL_1,
                'status' => Workflow::STATUS_IN_PROGRESS,
                'data' => $validated,
                'sjo' => null,
                'it' => null,
                'checks' => null,
                'user_id' => $user->id,
            ]);

            OrderLog::create([
                'sales_order_id' => $order->id,
                'user_id' => $user->id,
                'action' => 'created',
                'from_step' => null,
                'to_step' => Workflow::STEP_ASO_APPROVAL_1,
                'notes' => "ASO Form filed by {$validated['bds_name']} for {$validated['client_name']}. Total: " . Workflow::formatMoney($validated['total_project_amount']),
                'payload' => $validated,
            ]);

            return $order;
        });

        // Send email to holders of the 1st ASO approval role (Jovy, Fred)
        $this->notifyNextStepUsers($order, 'created', Auth::user()->name, 'New ASO Sales Order submitted and ready for 1st approval.');

        return redirect()->route('admin.sales-orders.show', $order)
            ->with('status', "Advertising Sales Order (ASO {$order->aso_number}) filed successfully! Forwarded to Jovy / Fred for 1st approval.");
    }

    /**
     * Show detailed order workflow page with interactive actions.
     */
    public function show(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load(['user', 'logs.user']);

        $amortizationSchedule = $salesOrder->getAmortizationSchedule();
        $milestones = $salesOrder->getMilestones();
        $workflowSteps = Workflow::steps();
        $accountingItems = Workflow::accountingChecklistItems();
        $sjoFields = Workflow::sjoFields();

        // Check if current user can act on the current step
        $canAct = $salesOrder->canBeActedOnBy(Auth::user());

        return view('admin.sales-orders.show', [
            'order' => $salesOrder,
            'workflowSteps' => $workflowSteps,
            'amortizationSchedule' => $amortizationSchedule,
            'milestones' => $milestones,
            'accountingItems' => $accountingItems,
            'sjoFields' => $sjoFields,
            'canAct' => $canAct,
        ]);
    }

    /**
     * Advance / Approve order at current step.
     */
    public function advance(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('actOnStep', [$salesOrder, $salesOrder->current_step]);

        $currentStep = $salesOrder->current_step;
        $stepDef = Workflow::steps()[$currentStep] ?? null;

        if (!$stepDef) {
            abort(400, 'Invalid workflow step.');
        }

        $user = Auth::user();
        $notes = $request->input('notes');

        // Specific validation & handling for form-based steps
        if ($currentStep === Workflow::STEP_SJO_CREATION) {
            $validatedSjo = $request->validate([
                'client_company' => ['required', 'string', 'max:255'],
                'advertising_material' => ['required', 'string'],
                'spot_number' => ['required', 'string', 'max:100'],
                'block_timer' => ['required', 'string', 'max:100'],
                'content_attachment_link' => ['required', 'url'],
                'special_instructions' => ['nullable', 'string'],
            ]);

            $sjoNumber = $salesOrder->sjo_number ?: SalesOrder::generateNextSjoNumber();
            $salesOrder->sjo_number = $sjoNumber;
            $salesOrder->sjo = $validatedSjo;
            $notes = "Job Order (SJO {$sjoNumber}) generated and submitted by {$user->name}.";
        } elseif ($currentStep === Workflow::STEP_IT_UPLOAD) {
            $validatedIt = $request->validate([
                'assigned_it' => ['required', 'string', 'max:150'],
                'material_link' => ['required', 'url'],
                'spots' => ['required', 'string', 'max:100'],
                'broadcast_type' => ['required', 'in:Paid,JV,Internal'],
                'monitoring_notes' => ['nullable', 'string'],
            ]);

            $salesOrder->it = $validatedIt;
            $notes = "IT Monitoring and player configuration uploaded by {$user->name}. Assigned IT: {$validatedIt['assigned_it']}. Type: {$validatedIt['broadcast_type']}.";
        } elseif ($currentStep === Workflow::STEP_ACCOUNTING_CHECK) {
            $checklist = $request->input('checklist', []);
            $requiredItems = array_keys(Workflow::accountingChecklistItems());

            // Ensure all checklist items are verified
            $allChecked = true;
            foreach ($requiredItems as $itemKey) {
                if (empty($checklist[$itemKey])) {
                    $allChecked = false;
                    break;
                }
            }

            if (!$allChecked) {
                return back()->withErrors([
                    'checklist' => 'All accounting checklist compliance items must be verified before final completion.',
                ]);
            }

            $salesOrder->checks = [
                'verified_by' => $user->name,
                'verified_at' => now()->toDateTimeString(),
                'items' => $checklist,
                'notes' => $notes,
            ];
        }

        // Determine next step
        $nextStep = $stepDef['next_step'] ?? Workflow::STEP_COMPLETED;

        DB::transaction(function () use ($salesOrder, $currentStep, $nextStep, $user, $notes, $request) {
            $salesOrder->current_step = $nextStep;

            if ($nextStep === Workflow::STEP_COMPLETED) {
                $salesOrder->status = Workflow::STATUS_COMPLETE;
                $salesOrder->completed_at = now();
            }

            $salesOrder->save();

            OrderLog::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => $user->id,
                'action' => ($nextStep === Workflow::STEP_COMPLETED) ? 'completed' : 'approved',
                'from_step' => $currentStep,
                'to_step' => $nextStep,
                'notes' => $notes ?: "Approved by {$user->name} at {$salesOrder->step_info['title']}.",
                'payload' => $request->except(['_token']),
            ]);
        });

        // Notify next role holders (or notify filer if complete)
        if ($nextStep === Workflow::STEP_COMPLETED) {
            $this->notifyNextStepUsers($salesOrder, 'completed', $user->name, 'All steps verified. Sales order complete!');
        } else {
            $this->notifyNextStepUsers($salesOrder, 'advanced', $user->name, $notes);
        }

        return redirect()->route('admin.sales-orders.show', $salesOrder)
            ->with('status', "Step sign-off successful! Order advanced to: {$salesOrder->step_info['title']}.");
    }

    /**
     * Decline order (stores status "Declined" with timestamp and reason).
     */
    public function decline(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('actOnStep', [$salesOrder, $salesOrder->current_step]);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $fromStep = $salesOrder->current_step;

        DB::transaction(function () use ($salesOrder, $fromStep, $user, $validated) {
            $salesOrder->status = Workflow::STATUS_DECLINED;
            $salesOrder->current_step = Workflow::STEP_DECLINED;
            $salesOrder->declined_at = now();
            $salesOrder->save();

            OrderLog::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => $user->id,
                'action' => 'declined',
                'from_step' => $fromStep,
                'to_step' => Workflow::STEP_DECLINED,
                'notes' => "Declined by {$user->name}: {$validated['reason']}",
                'payload' => ['reason' => $validated['reason']],
            ]);
        });

        // Send email to the order filer immediately
        if ($salesOrder->user) {
            $salesOrder->user->notify(new OrderStepNotification(
                salesOrder: $salesOrder,
                event: 'declined',
                actorName: $user->name,
                notes: $validated['reason']
            ));
        }

        return redirect()->route('admin.sales-orders.show', $salesOrder)
            ->with('status', "Sales order has been marked as DECLINED. Filer ({$salesOrder->bds_name}) has been notified via email.");
    }

    /**
     * Send order back a step (used in Content creative check / client approval).
     */
    public function sendBack(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('actOnStep', [$salesOrder, $salesOrder->current_step]);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $stepDef = Workflow::steps()[$salesOrder->current_step] ?? null;
        if (!$stepDef || empty($stepDef['can_send_back'])) {
            abort(400, 'This step does not support sending back.');
        }

        $user = Auth::user();
        $fromStep = $salesOrder->current_step;
        $prevStep = $stepDef['send_back_step'];

        DB::transaction(function () use ($salesOrder, $fromStep, $prevStep, $user, $validated) {
            $salesOrder->current_step = $prevStep;
            $salesOrder->save();

            OrderLog::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => $user->id,
                'action' => 'sent_back',
                'from_step' => $fromStep,
                'to_step' => $prevStep,
                'notes' => "Sent back to {$salesOrder->step_info['title']} by {$user->name}. Reason: {$validated['reason']}",
                'payload' => ['reason' => $validated['reason']],
            ]);
        });

        // Notify role holders of previous step
        $this->notifyNextStepUsers($salesOrder, 'sent_back', $user->name, $validated['reason']);

        return redirect()->route('admin.sales-orders.show', $salesOrder)
            ->with('status', "Order returned to previous step ({$salesOrder->step_info['title']}) for required revisions.");
    }

    /**
     * Update Accounting Verification checklist without advancing immediately.
     */
    public function updateChecks(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('actOnStep', [$salesOrder, Workflow::STEP_ACCOUNTING_CHECK]);

        $checklist = $request->input('checklist', []);
        $notes = $request->input('notes');
        $user = Auth::user();

        $salesOrder->checks = [
            'updated_by' => $user->name,
            'updated_at' => now()->toDateTimeString(),
            'items' => $checklist,
            'notes' => $notes,
        ];
        $salesOrder->save();

        OrderLog::create([
            'sales_order_id' => $salesOrder->id,
            'user_id' => $user->id,
            'action' => 'updated_checks',
            'from_step' => Workflow::STEP_ACCOUNTING_CHECK,
            'to_step' => Workflow::STEP_ACCOUNTING_CHECK,
            'notes' => "Accounting compliance checklist updated by {$user->name}.",
            'payload' => ['checklist' => $checklist, 'notes' => $notes],
        ]);

        return redirect()->route('admin.sales-orders.show', $salesOrder)
            ->with('status', 'Accounting checklist progress saved successfully.');
    }

    /**
     * Reports Screen (Admins and Accounting only).
     */
    public function reports(Request $request): View
    {
        if (!Schema::hasTable('sales_orders')) {
            return view('admin.sales-orders.migration-needed');
        }

        $this->authorize('viewReports', SalesOrder::class);

        // Core metric aggregates
        $totalOrders = SalesOrder::count();
        $inProgressOrders = SalesOrder::where('status', Workflow::STATUS_IN_PROGRESS)->count();
        $declinedOrders = SalesOrder::where('status', Workflow::STATUS_DECLINED)->count();
        $completeOrders = SalesOrder::where('status', Workflow::STATUS_COMPLETE)->count();

        $approvedContractValue = SalesOrder::where('status', Workflow::STATUS_COMPLETE)->sum('total_amount');
        $valueAwaitingApproval = SalesOrder::where('status', Workflow::STATUS_IN_PROGRESS)->sum('total_amount');
        $totalPipelineValue = SalesOrder::whereIn('status', [Workflow::STATUS_IN_PROGRESS, Workflow::STATUS_COMPLETE])->sum('total_amount');

        // Orders per step count
        $ordersPerStep = [];
        foreach (Workflow::steps() as $stepKey => $stepDef) {
            $ordersPerStep[$stepKey] = [
                'title' => $stepDef['title'],
                'stage' => $stepDef['stage'],
                'count' => SalesOrder::where('current_step', $stepKey)->count(),
                'value' => SalesOrder::where('current_step', $stepKey)->sum('total_amount'),
            ];
        }

        // Accrual Basis (Cost Base on PO) & Payment Milestones (Progress Billing + 10% Retention)
        $retentionRate = Workflow::RETENTION_PERCENTAGE; // 10%
        $totalRetentionAmount = round($totalPipelineValue * $retentionRate, 2);
        $totalProgressBillingAmount = round($totalPipelineValue - $totalRetentionAmount, 2);

        // Sales by BDS (Orders count and total sales)
        $salesByBds = SalesOrder::select('bds_name', 'bds_email', DB::raw('count(*) as order_count'), DB::raw('sum(total_amount) as total_sales'))
            ->groupBy('bds_name', 'bds_email')
            ->orderByDesc('total_sales')
            ->get();

        // Consolidated Monthly Amortization Schedule (spread evenly across active/complete campaigns)
        $monthlyAmortization = [];
        $activeOrders = SalesOrder::whereIn('status', [Workflow::STATUS_IN_PROGRESS, Workflow::STATUS_COMPLETE])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        foreach ($activeOrders as $ord) {
            $ordSchedule = $ord->getAmortizationSchedule();
            foreach ($ordSchedule as $entry) {
                $m = $entry['month_label'];
                if (!isset($monthlyAmortization[$m])) {
                    $monthlyAmortization[$m] = [
                        'month_label' => $m,
                        'total_amount' => 0,
                        'order_count' => 0,
                    ];
                }
                $monthlyAmortization[$m]['total_amount'] += $entry['amount'];
                $monthlyAmortization[$m]['order_count'] += 1;
            }
        }

        return view('admin.sales-orders.reports', [
            'totalOrders' => $totalOrders,
            'inProgressOrders' => $inProgressOrders,
            'declinedOrders' => $declinedOrders,
            'completeOrders' => $completeOrders,
            'approvedContractValue' => $approvedContractValue,
            'valueAwaitingApproval' => $valueAwaitingApproval,
            'totalPipelineValue' => $totalPipelineValue,
            'ordersPerStep' => $ordersPerStep,
            'retentionRate' => $retentionRate,
            'totalRetentionAmount' => $totalRetentionAmount,
            'totalProgressBillingAmount' => $totalProgressBillingAmount,
            'salesByBds' => $salesByBds,
            'monthlyAmortization' => $monthlyAmortization,
        ]);
    }

    /**
     * Helper to dispatch immediate real emails to all users holding the next step's role.
     */
    protected function notifyNextStepUsers(SalesOrder $order, string $event, ?string $actorName = null, ?string $notes = null): void
    {
        $stepInfo = $order->step_info;
        $requiredRoles = $stepInfo['roles'] ?? [];

        if (empty($requiredRoles)) {
            // If completed, notify order filer and accounting
            if ($event === 'completed') {
                $accountingUsers = User::whereHas('roles', fn ($q) => $q->where('slug', Workflow::ROLE_ACCOUNTING))->get();
                $recipients = collect([$order->user])->merge($accountingUsers)->filter()->unique('id');
                Notification::send($recipients, new OrderStepNotification($order, $event, $actorName, $notes));
            }
            return;
        }

        // Get users holding any of the required roles
        $usersToNotify = User::whereHas('roles', function ($q) use ($requiredRoles) {
            $q->whereIn('slug', $requiredRoles);
        })->get();

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new OrderStepNotification($order, $event, $actorName, $notes));
        }
    }
}
