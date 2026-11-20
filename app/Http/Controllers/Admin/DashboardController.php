<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SrfRequisition;
use App\Models\TransactionNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $srfRequisitions = SrfRequisition::with(['requester', 'verifier'])->latest()->get();
        $groupedSrfRequisitions = $srfRequisitions->groupBy('srf_number');

        $maxSequence = 956;
        foreach ($srfRequisitions->pluck('srf_number') as $number) {
            if (preg_match('/(\d{4})$/', trim($number), $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        $distinctSrfCount = $groupedSrfRequisitions->count();
        $userId = Auth::id();
        $notificationRecipients = fn ($query) => $query
            ->whereNull('user_id')
            ->orWhere('user_id', $userId);

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalRoles' => Role::count(),
            'adminCount' => User::whereHas('roles', fn ($query) => $query->whereIn('slug', ['it-admin', 'warehouse-admin']))->count(),
            'staffCount' => User::whereHas('roles', fn ($query) => $query->where('slug', 'warehouse-staff'))->count(),
            'technicalCount' => User::whereHas('roles', fn ($query) => $query->whereIn('slug', ['technical', 'technical-staff']))->count(),
            'recentUsers' => User::with('roles')->latest()->take(6)->get(),
            'onlineUsersCount' => DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
                ->distinct('user_id')
                ->count('user_id'),
            'srfRequisitions' => $srfRequisitions,
            'groupedSrfRequisitions' => $groupedSrfRequisitions,
            'srfPendingCount' => $srfRequisitions->whereIn('status', ['pending', 'for_approval'])->unique('srf_number')->count(),
            'srfCompletedCount' => $srfRequisitions->whereIn('status', ['completed', 'verified', 'approved'])->unique('srf_number')->count(),
            'srfDeclinedCount' => $srfRequisitions->whereIn('status', ['declined', 'rejected'])->unique('srf_number')->count(),
            'srfOnHoldCount' => $srfRequisitions->where('status', 'on_hold')->unique('srf_number')->count(),
            'srfTotalCount' => $distinctSrfCount,
            'userSrfCount' => $srfRequisitions->where('user_id', Auth::id())->unique('srf_number')->count(),
            'userSrfPendingCount' => $srfRequisitions->where('user_id', Auth::id())->whereIn('status', ['pending', 'for_approval'])->unique('srf_number')->count(),
            'userSrfCompletedCount' => $srfRequisitions->where('user_id', Auth::id())->whereIn('status', ['completed', 'verified', 'approved'])->unique('srf_number')->count(),
            'headerNotifications' => TransactionNotification::where($notificationRecipients)->latest()->take(3)->get(),
            'unreadNotificationCount' => TransactionNotification::where($notificationRecipients)->where('is_read', false)->count(),
            'autoSrfNumber' => date('Y').' - '.str_pad($maxSequence + 1, 4, '0', STR_PAD_LEFT),
            'autoSsoNumber' => 'SSO-'.date('Y').'-'.str_pad($distinctSrfCount + 101, 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function reports(): View
    {
        $user = Auth::user();
        $srfQuery = SrfRequisition::query();

        if (! $user->canViewWarehouseOperations() && ! $user->isItAdmin()) {
            $srfQuery->where('user_id', $user->id);
        }

        return view('admin.reports.index', [
            'srfCount' => $srfQuery->distinct('srf_number')->count('srf_number'),
        ]);
    }

    public function approvedSrf(Request $request): View
    {
        $query = SrfRequisition::with(['requester', 'verifier'])
            ->whereIn('status', ['completed', 'verified', 'approved'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($query) use ($search) {
                $query->where('srf_number', 'like', "%{$search}%")
                    ->orWhere('sso_number', 'like', "%{$search}%")
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('prepared_by', 'like', "%{$search}%")
                    ->orWhere('verified_by_name', 'like', "%{$search}%")
                    ->orWhere('item_model', 'like', "%{$search}%")
                    ->orWhere('item_manufacturer', 'like', "%{$search}%");
            });
        }

        $allApproved = $query->get();

        return view('admin.srf.approved', [
            'groupedApproved' => $allApproved->groupBy('srf_number'),
            'totalApprovedCount' => SrfRequisition::whereIn('status', ['completed', 'verified', 'approved'])
                ->distinct('srf_number')->count('srf_number'),
            'totalApprovedUnits' => SrfRequisition::whereIn('status', ['completed', 'verified', 'approved'])->sum('quantity'),
            'search' => $request->input('search'),
        ]);
    }

    public function srfPdf(string $srfNumber): View
    {
        $srfNumber = urldecode($srfNumber);
        $items = SrfRequisition::with(['requester', 'verifier'])
            ->where('srf_number', $srfNumber)
            ->get();

        abort_if($items->isEmpty(), 404, 'Stock Requisition Form not found.');

        $letterheadPath = public_path('images/letterhead.png');
        $letterheadBase64 = file_exists($letterheadPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($letterheadPath))
            : null;

        return view('admin.srf.pdf', [
            'srfNumber' => $srfNumber,
            'items' => $items,
            'first' => $items->first(),
            'letterheadBase64' => $letterheadBase64,
        ]);
    }

    public function processInstallationSrf(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageSrf(), 403);

        $validated = $request->validate([
            'client' => ['nullable', 'string', 'max:255'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'date_needed' => ['nullable', 'date'],
            'requisition_date' => ['nullable', 'date'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'sso_number' => ['nullable', 'string', 'max:100'],
            'srf_number' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_model' => ['required', 'string', 'max:255'],
            'items.*.item_manufacturer' => ['nullable', 'string', 'max:255'],
            'items.*.item_category' => ['nullable', 'string', 'max:255'],
            'items.*.item_description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.uom' => ['nullable', 'string', 'max:50'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
            'department' => ['nullable', 'string', 'max:100'],
            'prepared_by' => ['required', 'string', 'max:100'],
            'noted_by' => ['nullable', 'string', 'max:100'],
            'pre_approved_by' => ['nullable', 'string', 'max:100'],
            'approved_by' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $maxSequence = SrfRequisition::pluck('srf_number')
            ->map(fn (string $number): int => preg_match('/(\d{4})$/', trim($number), $matches) ? (int) $matches[1] : 956)
            ->max();
        $distinctSrfCount = SrfRequisition::distinct('srf_number')->count('srf_number');

        $validated['srf_number'] = ($validated['srf_number'] ?? null) ?: date('Y').' - '.str_pad(max(956, $maxSequence) + 1, 4, '0', STR_PAD_LEFT);
        $validated['sso_number'] = ($validated['sso_number'] ?? null) ?: 'SSO-'.date('Y').'-'.str_pad($distinctSrfCount + 101, 4, '0', STR_PAD_LEFT);
        $validated['project_name'] = ($validated['project_name'] ?? null) ?: (($validated['client'] ?? null) ?: 'Installation Project');
        $validated['client'] = ($validated['client'] ?? null) ?: $validated['project_name'];
        $validated['po_number'] = ($validated['po_number'] ?? null) ?: $validated['srf_number'];
        $validated['requisition_date'] = ($validated['requisition_date'] ?? null) ?: date('Y-m-d');
        $validated['department'] = ($validated['department'] ?? null) ?: 'GENERAL';
        $validated['approved_by'] = ($validated['approved_by'] ?? null) ?: 'Macy Guido Lee';

        DB::transaction(function () use ($validated, $request): void {
            foreach ($validated['items'] as $item) {
                SrfRequisition::create([
                    'srf_number' => $validated['srf_number'],
                    'sso_number' => $validated['sso_number'],
                    'project_name' => $validated['project_name'],
                    'client' => $validated['client'],
                    'po_number' => $validated['po_number'],
                    'date_needed' => $validated['date_needed'] ?? null,
                    'requisition_date' => $validated['requisition_date'],
                    'item_model' => $item['item_model'],
                    'item_manufacturer' => $item['item_manufacturer'] ?? null,
                    'item_category' => $item['item_category'] ?? null,
                    'item_description' => $item['item_description'] ?? null,
                    'quantity' => $item['quantity'],
                    'uom' => ($item['uom'] ?? null) ?: 'PCS',
                    'stock_status' => 'not_tracked',
                    'status' => 'pending',
                    'department' => $validated['department'],
                    'prepared_by' => $validated['prepared_by'],
                    'noted_by' => $validated['noted_by'] ?? null,
                    'pre_approved_by' => $validated['pre_approved_by'] ?? null,
                    'approved_by' => $validated['approved_by'],
                    'user_id' => $request->user()->id,
                    'remarks' => $item['remarks'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        TransactionNotification::log(
            title: 'Stock Requisition Form (SRF) Submitted',
            message: "{$validated['prepared_by']} submitted SRF #{$validated['srf_number']} with ".count($validated['items'])." item(s) for '{$validated['project_name']}'.",
            type: 'srf_submitted',
            referenceId: $validated['srf_number'],
            actor: $request->user()
        );

        return redirect()->route('admin.dashboard')->with('status', "SRF #{$validated['srf_number']} submitted for warehouse review.");
    }

    public function verifyInstallationSrf(Request $request, SrfRequisition $srf): RedirectResponse
    {
        abort_unless($request->user()?->canManageSrf(), 403);

        $validated = $request->validate([
            'decision' => ['nullable', 'string', 'in:approved,declined,on_hold'],
            'verification_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $validated['decision'] ?? 'approved';
        $user = $request->user();
        $requisitions = SrfRequisition::where('srf_number', $srf->srf_number)->get();

        foreach ($requisitions as $requisition) {
            $requisition->update([
                'status' => $decision,
                'verified_by_user_id' => $user->id,
                'verified_by_name' => $user->name,
                'verified_at' => now(),
                'verification_notes' => $validated['verification_notes'] ?? match ($decision) {
                    'approved' => 'Verified and approved by warehouse management.',
                    'declined' => 'Declined by warehouse management.',
                    default => 'Placed on hold pending further review.',
                },
            ]);
        }

        $decisionLabel = str_replace('_', ' ', strtoupper($decision));
        TransactionNotification::log(
            title: "SRF {$decisionLabel} by Warehouse",
            message: "Warehouse team member {$user->name} marked SRF #{$srf->srf_number} {$decisionLabel}.",
            type: 'srf_'.$decision,
            referenceId: $srf->srf_number,
            actor: $user
        );

        return redirect()->route('admin.dashboard')
            ->with('status', "SRF #{$srf->srf_number} marked {$decisionLabel} by {$user->name}.");
    }

    public function markNotificationRead(TransactionNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->user_id === null || (int) $notification->user_id === Auth::id(),
            403
        );

        $notification->update(['is_read' => true]);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllNotificationsRead(): RedirectResponse
    {
        $userId = Auth::id();
        TransactionNotification::where(function ($query) use ($userId) {
            $query->whereNull('user_id')->orWhere('user_id', $userId);
        })->update(['is_read' => true]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
