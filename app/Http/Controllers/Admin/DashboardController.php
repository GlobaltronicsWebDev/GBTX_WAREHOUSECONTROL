<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\SrfRequisition;
use App\Models\TransactionNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the warehouse admin dashboard overview.
     */
    public function index(Request $request): View
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $adminCount = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['it-admin', 'warehouse-admin']);
        })->count();
        $staffCount = User::whereHas('roles', function ($query) {
            $query->where('slug', 'warehouse-staff');
        })->count();
        $salesCount = User::whereHas('roles', function ($query) {
            $query->where('slug', 'sales-executive');
        })->count();
        $technicalCount = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['technical', 'technical-staff']);
        })->count();

        $recentUsers = User::with('roles')
            ->latest()
            ->take(6)
            ->get();

        $roles = Role::withCount('users')->get();

        $totalInventoryUnits = InventoryItem::sum('quantity');
        $totalInventoryModels = InventoryItem::count();

        $inventoryItems = InventoryItem::orderBy('model', 'asc')->get();
        $lowStockCount = InventoryItem::where('quantity', '<=', 5)->count();
        $defectiveCount = InventoryItem::where('location', 'DEFECTIVE')
            ->orWhere('status', 'maintenance')
            ->sum('quantity');

        $srfRequisitions = SrfRequisition::with(['inventoryItem', 'requester', 'verifier'])
            ->latest()
            ->get();
        $srfPendingCount = $srfRequisitions->whereIn('status', ['pending', 'for_approval'])->unique('srf_number')->count();
        $srfCompletedCount = $srfRequisitions->whereIn('status', ['completed', 'verified', 'approved'])->unique('srf_number')->count();
        $srfDeclinedCount = $srfRequisitions->whereIn('status', ['declined', 'rejected'])->unique('srf_number')->count();
        $srfOnHoldCount = $srfRequisitions->where('status', 'on_hold')->unique('srf_number')->count();
        $srfTotalCount = $srfRequisitions->unique('srf_number')->count();
        $groupedSrfRequisitions = $srfRequisitions->groupBy('srf_number');

        $currentUserId = Auth::id();
        $userSrfCount = $srfRequisitions->where('user_id', $currentUserId)->unique('srf_number')->count();
        $userSrfPendingCount = $srfRequisitions->where('user_id', $currentUserId)->whereIn('status', ['pending', 'for_approval'])->unique('srf_number')->count();
        $userSrfCompletedCount = $srfRequisitions->where('user_id', $currentUserId)->whereIn('status', ['completed', 'verified', 'approved'])->unique('srf_number')->count();

        $maxSequence = 956;
        $existingSrfNumbers = SrfRequisition::pluck('srf_number');
        foreach ($existingSrfNumbers as $num) {
            if (preg_match('/(\d{4})$/', trim($num), $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSequence) {
                    $maxSequence = $seq;
                }
            }
        }
        $nextSequence = $maxSequence + 1;
        $autoSrfNumber = date('Y').' - '.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        $distinctSrfCount = SrfRequisition::distinct('srf_number')->count('srf_number');
        $autoSsoNumber = 'SSO-'.date('Y').'-'.str_pad($distinctSrfCount + 101, 4, '0', STR_PAD_LEFT);

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'adminCount' => $adminCount,
            'staffCount' => $staffCount,
            'salesCount' => $salesCount,
            'technicalCount' => $technicalCount,
            'recentUsers' => $recentUsers,
            'roles' => $roles,
            'totalInventoryUnits' => $totalInventoryUnits,
            'totalInventoryModels' => $totalInventoryModels,
            'inventoryItems' => $inventoryItems,
            'lowStockCount' => $lowStockCount,
            'defectiveCount' => $defectiveCount,
            'srfRequisitions' => $srfRequisitions,
            'groupedSrfRequisitions' => $groupedSrfRequisitions,
            'srfPendingCount' => $srfPendingCount,
            'srfCompletedCount' => $srfCompletedCount,
            'srfDeclinedCount' => $srfDeclinedCount,
            'srfOnHoldCount' => $srfOnHoldCount,
            'srfTotalCount' => $srfTotalCount,
            'userSrfCount' => $userSrfCount,
            'userSrfPendingCount' => $userSrfPendingCount,
            'userSrfCompletedCount' => $userSrfCompletedCount,
            'autoSrfNumber' => $autoSrfNumber,
            'autoSsoNumber' => $autoSsoNumber,
        ]);
    }

    /**
     * View summary of Approved & Verified SRF requisitions.
     */
    public function approvedSrf(Request $request): View
    {
        $query = SrfRequisition::with(['inventoryItem', 'requester', 'verifier'])
            ->whereIn('status', ['completed', 'verified', 'approved'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('srf_number', 'like', "%{$search}%")
                    ->orWhere('sso_number', 'like', "%{$search}%")
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('prepared_by', 'like', "%{$search}%")
                    ->orWhere('verified_by_name', 'like', "%{$search}%")
                    ->orWhereHas('inventoryItem', function ($sub) use ($search) {
                        $sub->where('model', 'like', "%{$search}%")
                            ->orWhere('manufacturer', 'like', "%{$search}%");
                    });
            });
        }

        $allApproved = $query->get();
        $groupedApproved = $allApproved->groupBy('srf_number');
        $totalApprovedCount = SrfRequisition::whereIn('status', ['completed', 'verified', 'approved'])->distinct('srf_number')->count('srf_number');
        $totalApprovedUnits = SrfRequisition::whereIn('status', ['completed', 'verified', 'approved'])->sum('quantity');

        return view('admin.srf.approved', [
            'groupedApproved' => $groupedApproved,
            'totalApprovedCount' => $totalApprovedCount,
            'totalApprovedUnits' => $totalApprovedUnits,
            'search' => $request->input('search'),
        ]);
    }

    /**
     * Downloadable / Printable PDF document for an SRF Requisition.
     */
    public function srfPdf(string $srfNumber): View
    {
        $srfNumber = urldecode($srfNumber);

        $items = SrfRequisition::with(['inventoryItem', 'requester', 'verifier'])
            ->where('srf_number', $srfNumber)
            ->get();

        if ($items->isEmpty()) {
            abort(404, 'Stock Requisition Form not found.');
        }

        $letterheadPath = public_path('images/letterhead.png');
        $letterheadBase64 = null;
        if (file_exists($letterheadPath)) {
            $letterheadBase64 = 'data:image/png;base64,'.base64_encode((string) file_get_contents($letterheadPath));
        }

        return view('admin.srf.pdf', [
            'srfNumber' => $srfNumber,
            'items' => $items,
            'first' => $items->first(),
            'letterheadBase64' => $letterheadBase64,
        ]);
    }

    /**
     * Process order picking and dispatch (Post Order / Pre Order Online).
     */
    public function dispatchOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'order_number' => ['required', 'string', 'max:100'],
            'recipient' => ['required', 'string', 'max:255'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);

        if ($item->quantity < $validated['quantity']) {
            return back()->withErrors([
                'dispatch_error' => "Insufficient stock for '{$item->model}'. Available: {$item->quantity} units, requested: {$validated['quantity']} units.",
            ]);
        }

        if ($item->sqm && $item->quantity > 0) {
            $unitSqm = $item->sqm / $item->quantity;
            $newSqm = max(0, round($item->sqm - ($validated['quantity'] * $unitSqm), 2));
            $item->sqm = $newSqm;
        }

        $item->quantity -= $validated['quantity'];
        if ($item->quantity === 0) {
            $item->status = 'out_of_stock';
        } elseif ($item->quantity <= 5) {
            $item->status = 'low_stock';
        }
        $item->save();

        TransactionNotification::log(
            title: "Order Dispatched (#{$validated['order_number']})",
            message: "Dispatched {$validated['quantity']} units of '{$item->model}' for Order #{$validated['order_number']} to {$validated['recipient']}.",
            type: 'dispatch',
            referenceId: $validated['order_number'],
            actor: Auth::user()
        );

        return redirect()->route('admin.dashboard')
            ->with('status', "Order #{$validated['order_number']} successfully dispatched! Picked & loaded {$validated['quantity']} units of '{$item->model}' ({$item->manufacturer}) for recipient '{$validated['recipient']}'.");
    }

    /**
     * Conduct inventory cycle count & discrepancy adjustment.
     */
    public function cycleCount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'counted_quantity' => ['required', 'integer', 'min:0'],
            'auditor_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        $oldQty = $item->quantity;
        $variance = $validated['counted_quantity'] - $oldQty;

        if ($item->sqm && $oldQty > 0) {
            $unitSqm = $item->sqm / $oldQty;
            $item->sqm = round($validated['counted_quantity'] * $unitSqm, 2);
        }

        $item->quantity = $validated['counted_quantity'];
        if ($item->quantity === 0) {
            $item->status = 'out_of_stock';
        } elseif ($item->quantity <= 5) {
            $item->status = 'low_stock';
        } else {
            $item->status = 'in_stock';
        }
        $item->save();

        $varianceText = $variance === 0
            ? 'Count matched 100% (No variance)'
            : ($variance > 0 ? "+{$variance} units variance adjusted" : "{$variance} units variance documented");

        TransactionNotification::log(
            title: "Cycle Count Audited: {$item->model}",
            message: "Audited '{$item->model}' at {$item->location}: {$validated['counted_quantity']} pcs physically verified. {$varianceText}.",
            type: 'cycle_count',
            referenceId: "ITEM-{$item->id}",
            actor: Auth::user()
        );

        return redirect()->route('admin.dashboard')
            ->with('status', "Cycle count verified for '{$item->model}' at {$item->location}. New stock: {$item->quantity} units. {$varianceText}.");
    }

    /**
     * Record returned items / Reverse Logistics (quarantine or restock).
     */
    public function returnItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'disposition' => ['required', 'in:restock,quarantine_defective,dispose'],
            'serial_numbers' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);

        if ($validated['disposition'] === 'restock') {
            $item->quantity += $validated['quantity'];
            if ($item->status === 'out_of_stock' || $item->status === 'low_stock') {
                $item->status = $item->quantity <= 5 ? 'low_stock' : 'in_stock';
            }
            $item->save();

            TransactionNotification::log(
                title: "Return Restocked: {$item->model}",
                message: "Restocked {$validated['quantity']} units of '{$item->model}' to active storage at {$item->location}.",
                type: 'return',
                referenceId: "ITEM-{$item->id}",
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Return accepted and restocked: {$validated['quantity']} units of '{$item->model}' returned to active stock at {$item->location}.");
        } elseif ($validated['disposition'] === 'quarantine_defective') {
            InventoryItem::create([
                'category' => $item->category,
                'tag_number' => 'TAG-DEF-'.strtoupper(bin2hex(random_bytes(3))),
                'po_number' => $item->po_number ? $item->po_number.'-RMA' : 'RMA-'.date('Ymd'),
                'manufacturer' => $item->manufacturer,
                'check_in_date' => date('Y-m-d'),
                'model' => $item->model.' [DEFECTIVE / RMA]',
                'screen_size' => $item->screen_size,
                'item_description' => 'DEFECTIVE RETURN: '.$validated['reason'].($validated['serial_numbers'] ? ' | Serials: '.$validated['serial_numbers'] : ''),
                'quantity' => $validated['quantity'],
                'sqm' => 0,
                'location' => 'DEFECTIVE',
                'status' => 'maintenance',
                'created_by' => Auth::id(),
            ]);

            TransactionNotification::log(
                title: "Defective Return RMA: {$item->model}",
                message: "Quarantined {$validated['quantity']} defective units of '{$item->model}' in DEFECTIVE bay. Reason: {$validated['reason']}.",
                type: 'return_rma',
                referenceId: "ITEM-{$item->id}",
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Defective return isolated: {$validated['quantity']} units of '{$item->model}' placed in designated DEFECTIVE quarantine hold area. RMA recorded.");
        } else {
            TransactionNotification::log(
                title: "Material Disposal: {$item->model}",
                message: "Recorded disposal for {$validated['quantity']} units of '{$item->model}'. Reason: {$validated['reason']}.",
                type: 'disposal',
                referenceId: "ITEM-{$item->id}",
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Disposal recorded for {$validated['quantity']} units of '{$item->model}'. Reason: {$validated['reason']}.");
        }
    }

    /**
     * Step 1-3: Process Stock Requisition Form (SRF) & Availability Check.
     */
    public function processInstallationSrf(Request $request): RedirectResponse
    {
        $maxSequence = 956;
        $existingSrfNumbers = SrfRequisition::pluck('srf_number');
        foreach ($existingSrfNumbers as $num) {
            if (preg_match('/(\d{4})$/', trim($num), $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSequence) {
                    $maxSequence = $seq;
                }
            }
        }
        $nextSequence = $maxSequence + 1;
        $defaultSrfNumber = date('Y').' - '.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        $distinctSrfCount = SrfRequisition::distinct('srf_number')->count('srf_number');
        $defaultSsoNumber = 'SSO-'.date('Y').'-'.str_pad($distinctSrfCount + 101, 4, '0', STR_PAD_LEFT);

        $validated = $request->validate([
            'client' => ['nullable', 'string', 'max:255'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'date_needed' => ['nullable', 'date'],
            'requisition_date' => ['nullable', 'date'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'sso_number' => ['nullable', 'string', 'max:100'],
            'srf_number' => ['nullable', 'string', 'max:100'],
            'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'uom' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.uom' => ['nullable', 'string', 'max:50'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
            'department' => ['nullable', 'string', 'max:100'],
            'prepared_by' => ['required', 'string', 'max:100'],
            'noted_by' => ['nullable', 'string', 'max:100'],
            'pre_approved_by' => ['nullable', 'string', 'max:100'],
            'approved_by' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['srf_number'] = ! empty($validated['srf_number']) ? $validated['srf_number'] : $defaultSrfNumber;
        $validated['sso_number'] = ! empty($validated['sso_number']) ? $validated['sso_number'] : $defaultSsoNumber;
        $validated['project_name'] = ! empty($validated['project_name']) ? $validated['project_name'] : (! empty($validated['client']) ? $validated['client'] : 'Installation Project');
        $validated['client'] = ! empty($validated['client']) ? $validated['client'] : $validated['project_name'];
        $validated['po_number'] = ! empty($validated['po_number']) ? $validated['po_number'] : $validated['srf_number'];
        $validated['uom'] = ! empty($validated['uom']) ? $validated['uom'] : 'PCS';
        $validated['remarks'] = $validated['remarks'] ?? null;
        $validated['requisition_date'] = ! empty($validated['requisition_date']) ? $validated['requisition_date'] : date('Y-m-d');
        $validated['date_needed'] = $validated['date_needed'] ?? null;
        $validated['department'] = ! empty($validated['department']) ? $validated['department'] : 'SALES';
        $validated['approved_by'] = $validated['approved_by'] ?? 'Macy Guido Lee';
        $validated['noted_by'] = $validated['noted_by'] ?? null;
        $validated['pre_approved_by'] = $validated['pre_approved_by'] ?? null;

        $itemsToProcess = [];
        if (! empty($validated['items']) && is_array($validated['items'])) {
            foreach ($validated['items'] as $it) {
                if (! empty($it['inventory_item_id'])) {
                    $itemsToProcess[] = [
                        'inventory_item_id' => (int) $it['inventory_item_id'],
                        'quantity' => max(1, (int) ($it['quantity'] ?? 1)),
                        'uom' => ! empty($it['uom']) ? $it['uom'] : $validated['uom'],
                        'remarks' => $it['remarks'] ?? $validated['remarks'],
                    ];
                }
            }
        }

        // Fallback for single item submissions
        if (empty($itemsToProcess) && ! empty($validated['inventory_item_id'])) {
            $itemsToProcess[] = [
                'inventory_item_id' => (int) $validated['inventory_item_id'],
                'quantity' => max(1, (int) ($validated['quantity'] ?? 1)),
                'uom' => ! empty($validated['uom']) ? $validated['uom'] : 'PCS',
                'remarks' => $validated['remarks'] ?? null,
            ];
        }

        if (empty($itemsToProcess)) {
            return redirect()->back()->withErrors(['inventory_item_id' => 'Please select at least one item to requisition.'])->withInput();
        }

        $processedItemDescriptions = [];
        $hasOutOfStock = false;
        $totalItemsCount = count($itemsToProcess);

        foreach ($itemsToProcess as $it) {
            $item = InventoryItem::findOrFail($it['inventory_item_id']);
            $processedItemDescriptions[] = "{$it['quantity']} {$it['uom']} of {$item->model}";
            $stockStatus = ($item->quantity >= $it['quantity']) ? 'available_reserved' : 'insufficient_pr_hold';

            if ($stockStatus === 'available_reserved') {
                // BRANCH YES: AVAILABLE in stock -> Reserve for Project
                $item->quantity -= $it['quantity'];
                if ($item->quantity === 0) {
                    $item->status = 'out_of_stock';
                } elseif ($item->quantity <= 5) {
                    $item->status = 'low_stock';
                }
                $item->save();

                InventoryItem::create([
                    'category' => $item->category,
                    'tag_number' => 'TAG-PRJ-'.strtoupper(bin2hex(random_bytes(3))),
                    'po_number' => $validated['po_number'],
                    'manufacturer' => $item->manufacturer,
                    'check_in_date' => date('Y-m-d'),
                    'model' => $item->model.' [RESERVED: '.$validated['project_name'].']',
                    'screen_size' => $item->screen_size,
                    'item_description' => "RESERVED FOR SRF: {$validated['project_name']} (Client: {$validated['client']}, PO: {$validated['po_number']}, SSO: {$validated['sso_number']}). Assignators: Prep: {$validated['prepared_by']}, Approved: {$validated['approved_by']}. Remarks: ".($it['remarks'] ?? 'None'),
                    'quantity' => $it['quantity'],
                    'sqm' => $item->sqm ? round(($item->sqm / max(1, $item->quantity + $it['quantity'])) * $it['quantity'], 2) : 0,
                    'location' => 'STAGE-BAY-01',
                    'status' => 'reserved',
                    'created_by' => Auth::id(),
                ]);
            } else {
                $hasOutOfStock = true;
                // BRANCH NO: OUT OF STOCK -> Trigger PR -> MRR -> ADD TO INVENTORY & RESERVE
                InventoryItem::create([
                    'category' => $item->category,
                    'tag_number' => 'TAG-PR-'.strtoupper(bin2hex(random_bytes(3))),
                    'po_number' => $validated['po_number'] ? $validated['po_number'] : ('PR-'.strtoupper(bin2hex(random_bytes(3)))),
                    'manufacturer' => $item->manufacturer,
                    'check_in_date' => date('Y-m-d'),
                    'model' => $item->model.' [PR/MRR PROCUREMENT]',
                    'screen_size' => $item->screen_size,
                    'item_description' => "PR/MRR INBOUND HOLD for {$validated['project_name']} (Client: {$validated['client']}). Shortfall of {$it['quantity']} {$it['uom']} against stock ({$item->quantity} pcs). Assignators: Joshua Labios (PR), Arbie Hipolito (MRR), Teddymar Bajeta (Apprv). Remarks: ".($it['remarks'] ?? 'None'),
                    'quantity' => $it['quantity'],
                    'sqm' => 0,
                    'location' => 'RECEIVING-HOLD',
                    'status' => 'reserved',
                    'created_by' => Auth::id(),
                ]);
            }

            SrfRequisition::create([
                'srf_number' => $validated['srf_number'],
                'sso_number' => $validated['sso_number'],
                'project_name' => $validated['project_name'],
                'client' => $validated['client'],
                'po_number' => $validated['po_number'],
                'date_needed' => $validated['date_needed'],
                'requisition_date' => $validated['requisition_date'],
                'inventory_item_id' => $item->id,
                'quantity' => $it['quantity'],
                'uom' => $it['uom'],
                'stock_status' => $stockStatus,
                'status' => 'pending',
                'department' => $validated['department'],
                'prepared_by' => $validated['prepared_by'],
                'noted_by' => $validated['noted_by'],
                'pre_approved_by' => $validated['pre_approved_by'],
                'approved_by' => $validated['approved_by'],
                'user_id' => Auth::id(),
                'remarks' => $it['remarks'],
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        $itemsSummaryStr = implode(', ', $processedItemDescriptions);
        if ($hasOutOfStock) {
            $statusMsg = "Stock Requisition Form (SRF #{$validated['srf_number']}) submitted with {$totalItemsCount} item(s)! Stock check: Some items require PR/MRR procurement hold. Status: PENDING warehouse review.";
        } else {
            $statusMsg = "Stock Requisition Form (SRF #{$validated['srf_number']}) submitted with {$totalItemsCount} item(s)! Stock check: ALL AVAILABLE IN STOCK. Staged at STAGE-BAY-01 for {$validated['project_name']}. Status: PENDING warehouse verification.";
        }

        TransactionNotification::log(
            title: 'Stock Requisition Form (SRF) Submitted',
            message: "{$validated['prepared_by']} submitted SRF #{$validated['srf_number']} with {$totalItemsCount} item(s) ({$itemsSummaryStr}) for '{$validated['project_name']}'. Status: PENDING Warehouse Review.",
            type: 'srf_submitted',
            referenceId: $validated['srf_number'],
            actor: Auth::user()
        );

        return redirect()->route('admin.dashboard')->with('status', $statusMsg);
    }

    /**
     * Verify, approve, decline, or hold an Installation SRF Requisition (Warehouse Admin or Staff).
     */
    public function verifyInstallationSrf(Request $request, SrfRequisition $srf): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['nullable', 'string', 'in:approved,declined,on_hold'],
            'verification_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $validated['decision'] ?? 'approved';
        $user = Auth::user();
        $requisitions = SrfRequisition::where('srf_number', $srf->srf_number)->get();

        if ($decision === 'approved') {
            foreach ($requisitions as $req) {
                $req->update([
                    'status' => 'approved',
                    'verified_by_user_id' => $user->id,
                    'verified_by_name' => $user->name,
                    'verified_at' => now(),
                    'verification_notes' => $validated['verification_notes'] ?? 'Verified and approved by warehouse management.',
                ]);
            }

            TransactionNotification::log(
                title: 'SRF Approved by Warehouse',
                message: "Warehouse team member {$user->name} approved SRF #{$srf->srf_number} for project '{$srf->project_name}'. Status updated to APPROVED.",
                type: 'srf_approved',
                referenceId: $srf->srf_number,
                actor: $user
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "SRF #{$srf->srf_number} for project '{$srf->project_name}' has been APPROVED by {$user->name}.");
        } elseif ($decision === 'declined') {
            foreach ($requisitions as $req) {
                $req->update([
                    'status' => 'declined',
                    'verified_by_user_id' => $user->id,
                    'verified_by_name' => $user->name,
                    'verified_at' => now(),
                    'verification_notes' => $validated['verification_notes'] ?? 'Declined by warehouse management.',
                ]);
            }

            TransactionNotification::log(
                title: 'SRF Declined by Warehouse',
                message: "Warehouse team member {$user->name} declined SRF #{$srf->srf_number} for project '{$srf->project_name}'. Status updated to DECLINED.",
                type: 'srf_declined',
                referenceId: $srf->srf_number,
                actor: $user
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "SRF #{$srf->srf_number} for project '{$srf->project_name}' has been DECLINED by {$user->name}.");
        } else { // on_hold
            foreach ($requisitions as $req) {
                $req->update([
                    'status' => 'on_hold',
                    'verified_by_user_id' => $user->id,
                    'verified_by_name' => $user->name,
                    'verified_at' => now(),
                    'verification_notes' => $validated['verification_notes'] ?? 'Placed on hold pending further review.',
                ]);
            }

            TransactionNotification::log(
                title: 'SRF Placed On Hold',
                message: "Warehouse team member {$user->name} placed SRF #{$srf->srf_number} for project '{$srf->project_name}' ON HOLD.",
                type: 'srf_on_hold',
                referenceId: $srf->srf_number,
                actor: $user
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "SRF #{$srf->srf_number} for project '{$srf->project_name}' has been placed ON HOLD by {$user->name}.");
        }
    }

    /**
     * Step 5: Process Stock Transfer Out (STO) Inter-Facility Bay Transfer.
     */
    public function processInstallationSto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'sto_number' => ['required', 'string', 'max:100'],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'source_bay' => ['required', 'string', 'max:100'],
            'destination_bay' => ['required', 'string', 'max:100'],
            'prepared_by' => ['required', 'string', 'max:100'],
            'checked_by' => ['nullable', 'string', 'max:100'],
            'released_by' => ['nullable', 'string', 'max:100'],
            'received_by' => ['nullable', 'string', 'max:100'],
            'approved_by' => ['required', 'string', 'max:100'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        $item->location = $validated['destination_bay'];
        $item->save();

        $releasedBy = $validated['released_by'] ?? 'N/A';
        $approvedBy = $validated['approved_by'] ?? 'N/A';

        TransactionNotification::log(
            title: "Stock Transfer Out (STO #{$validated['sto_number']})",
            message: "Transferred {$validated['quantity']} units of {$item->model} from {$validated['source_bay']} to {$validated['destination_bay']} for project '{$validated['project_name']}'.",
            type: 'sto_transfer',
            referenceId: $validated['sto_number'],
            actor: Auth::user()
        );

        return redirect()->route('admin.dashboard')
            ->with('status', "Stock Transfer Out (STO #{$validated['sto_number']}) completed! {$validated['quantity']} units transferred from {$validated['source_bay']} to {$validated['destination_bay']} for project '{$validated['project_name']}'. Signatories verified: {$validated['prepared_by']} (Prep), {$releasedBy} (Released), {$approvedBy} (Approved).");
    }

    /**
     * Step 8-9: Generate Official Delivery Receipt (DR) & Site Release.
     */
    public function processInstallationDr(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'dr_number' => ['required', 'string', 'max:100'],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'recipient_destination' => ['required', 'string', 'max:255'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'prepared_by' => ['required', 'string', 'max:100'],
            'checked_by' => ['nullable', 'string', 'max:100'],
            'approved_by' => ['required', 'string', 'max:100'],
            'released_by' => ['nullable', 'string', 'max:100'],
            'received_by' => ['nullable', 'string', 'max:100'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);

        if ($item->quantity < $validated['quantity']) {
            return back()->withErrors([
                'dr_error' => "Cannot issue DR: Insufficient quantity in '{$item->model}'. Available: {$item->quantity}, requested: {$validated['quantity']}.",
            ]);
        }

        $item->quantity -= $validated['quantity'];
        if ($item->quantity === 0) {
            $item->status = 'out_of_stock';
        } elseif ($item->quantity <= 5) {
            $item->status = 'low_stock';
        }
        $item->save();

        $vehicle = $validated['vehicle_plate'] ?? 'N/A';
        $releasedBy = $validated['released_by'] ?? 'N/A';
        $receivedBy = $validated['received_by'] ?? 'N/A';

        TransactionNotification::log(
            title: "Delivery Receipt Issued (DR #{$validated['dr_number']})",
            message: "Dispatched {$validated['quantity']} units of '{$item->model}' for '{$validated['project_name']}' to {$validated['recipient_destination']} via {$vehicle}.",
            type: 'dr_dispatched',
            referenceId: $validated['dr_number'],
            actor: Auth::user()
        );

        return redirect()->route('admin.dashboard')
            ->with('status', "Delivery Receipt (DR #{$validated['dr_number']}) issued! Dispatched {$validated['quantity']} units of '{$item->model}' for installation project '{$validated['project_name']}' to site '{$validated['recipient_destination']}' via vehicle {$vehicle}. Released by {$releasedBy}, Received by {$receivedBy}.");
    }

    /**
     * Step 11: Process Return Slip & Defect Triage (Adjust Inventory or Add to EOL Items).
     */
    public function processInstallationReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'return_slip_number' => ['required', 'string', 'max:100'],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'disposition_flow' => ['required', 'in:no_returns_consumed,restock_good,technician_repair,eol_scrap'],
            'serial_numbers' => ['nullable', 'string', 'max:255'],
            'findings' => ['nullable', 'string', 'max:500'],
            'returned_by' => ['nullable', 'string', 'max:100'],
            'received_by' => ['nullable', 'string', 'max:100'],
            'approved_by' => ['nullable', 'string', 'max:100'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        $findings = $validated['findings'] ?? 'Standard pullout';
        $serials = $validated['serial_numbers'] ?? 'N/A';
        $receivedBy = $validated['received_by'] ?? 'N/A';
        $approvedBy = $validated['approved_by'] ?? 'N/A';

        if ($validated['disposition_flow'] === 'no_returns_consumed') {
            TransactionNotification::log(
                title: "Project Material Consumed: {$validated['project_name']}",
                message: "Project concluded with zero return pullouts. All {$validated['quantity']} units consumed onsite.",
                type: 'project_consumed',
                referenceId: $validated['return_slip_number'],
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Project '{$validated['project_name']}' concluded! Flow: RETURN ITEM? NO ➔ DEDUCT TO INVENTORY. All deployed materials successfully consumed onsite. Inventory booked.");
        } elseif ($validated['disposition_flow'] === 'restock_good') {
            $item->quantity += $validated['quantity'];
            $item->status = $item->quantity <= 5 ? 'low_stock' : 'in_stock';
            $item->save();

            TransactionNotification::log(
                title: "Project Return Restocked: {$item->model}",
                message: "Returned {$validated['quantity']} good units to active stock for Return Slip #{$validated['return_slip_number']}.",
                type: 'return_restock',
                referenceId: $validated['return_slip_number'],
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Return Slip #{$validated['return_slip_number']} processed! Flow: FOR REPAIR? NO ➔ ADJUST INVENTORY. {$validated['quantity']} units in good condition returned to active stock. Received by {$receivedBy}, Approved by {$approvedBy}.");
        } elseif ($validated['disposition_flow'] === 'technician_repair') {
            InventoryItem::create([
                'category' => $item->category,
                'tag_number' => 'TAG-RPR-'.strtoupper(bin2hex(random_bytes(3))),
                'po_number' => $validated['return_slip_number'],
                'manufacturer' => $item->manufacturer,
                'check_in_date' => date('Y-m-d'),
                'model' => $item->model.' [TECH REPAIR]',
                'screen_size' => $item->screen_size,
                'item_description' => "PULLOUT REPAIR HOLD: {$findings}. Returned from {$validated['project_name']}. Serials: {$serials}.",
                'quantity' => $validated['quantity'],
                'sqm' => 0,
                'location' => 'TECH-BENCH',
                'status' => 'maintenance',
                'created_by' => Auth::id(),
            ]);

            TransactionNotification::log(
                title: "Transferred to Technician: {$item->model}",
                message: "Transferred {$validated['quantity']} units of {$item->model} to Technician Bench for diagnostics (Return #{$validated['return_slip_number']}).",
                type: 'tech_repair',
                referenceId: $validated['return_slip_number'],
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Return Slip #{$validated['return_slip_number']} logged! Flow: FOR REPAIR? YES ➔ TRANSFER TO TECHNICIAN. {$validated['quantity']} defective units transferred to Technician Bench. Serials: {$serials}.");
        } else {
            // eol_scrap -> REPAIRED? NO ➔ ADD TO EOL ITEMS
            InventoryItem::create([
                'category' => 'EOL PHILIPS UNITS',
                'tag_number' => 'TAG-EOL-'.strtoupper(bin2hex(random_bytes(3))),
                'po_number' => $validated['return_slip_number'].'-EOL',
                'manufacturer' => $item->manufacturer,
                'check_in_date' => date('Y-m-d'),
                'model' => $item->model.' [DECOMMISSIONED EOL]',
                'screen_size' => $item->screen_size,
                'item_description' => "DECOMMISSIONED AFTER INSTALLATION: {$findings}. Deployed in {$validated['project_name']}, declared unrepairable.",
                'quantity' => $validated['quantity'],
                'sqm' => 0,
                'location' => 'EOL-SCRAP',
                'status' => 'out_of_stock',
                'created_by' => Auth::id(),
            ]);

            TransactionNotification::log(
                title: "Decommissioned to EOL Scrap: {$item->model}",
                message: "Decommissioned {$validated['quantity']} units to EOL Philips Units scrap category (Return #{$validated['return_slip_number']}).",
                type: 'eol_decommission',
                referenceId: $validated['return_slip_number'],
                actor: Auth::user()
            );

            return redirect()->route('admin.dashboard')
                ->with('status', "Return Slip #{$validated['return_slip_number']} finalized! Flow: REPAIRED? NO ➔ ADD TO EOL ITEMS. {$validated['quantity']} units verified beyond repair and decommissioned into EOL Items inventory.");
        }
    }

    /**
     * Mark a single notification as read.
     */
    public function markNotificationRead(TransactionNotification $notification): RedirectResponse
    {
        $notification->update(['is_read' => true]);

        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all notifications for current user/system as read.
     */
    public function markAllNotificationsRead(): RedirectResponse
    {
        $userId = Auth::id();
        TransactionNotification::where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        })->update(['is_read' => true]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
