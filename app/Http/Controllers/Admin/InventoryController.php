<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items with filters and metrics.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $manufacturer = $request->query('manufacturer');
        $category = $request->query('category', 'CENTRALIZED LED INVENTORY');
        $location = $request->query('location');
        $screenSize = $request->query('screen_size');
        $status = $request->query('status');

        $query = InventoryItem::with('creator')->orderBy('id', 'asc');

        $serviceUnitSubCategories = [
            'LED Service Units',
            'Philips Service Units',
            'Video Controllers / Processors',
            'Shuttle',
            'Aver',
            'Digital iPoster',
            'Kiosks',
        ];

        $isServiceUnitsParent = in_array($category, ['Service Units (Events, Demo)', 'SERVICE UNITS (EVENTS, DEMO)', 'Service Units', 'SERVICE UNITS']);

        $categoryFilterCallback = function ($q) use ($category, $isServiceUnitsParent, $serviceUnitSubCategories) {
            if ($isServiceUnitsParent) {
                $q->where(function ($sq) use ($serviceUnitSubCategories) {
                    $sq->whereIn('category', array_merge($serviceUnitSubCategories, ['Service Units (Events, Demo)', 'SERVICE UNITS (EVENTS, DEMO)', 'Service Units', 'SERVICE UNITS']))
                        ->orWhere('category', 'like', '%Service Unit%')
                        ->orWhere('category', 'like', '%SERVICE UNIT%');
                });
            } else {
                $q->where('category', $category);
            }
        };

        if ($category && $category !== 'all') {
            $categoryFilterCallback($query);
        }

        if ($location && $location !== 'all') {
            $query->where('location', $location);
        }

        if ($screenSize && $screenSize !== 'all') {
            $query->where('screen_size', $screenSize);
        }

        if ($manufacturer) {
            $query->where('manufacturer', $manufacturer);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->search($search);
        }

        // Limit to 10 items per page with next page navigation
        $perPage = (int) $request->query('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

        $totalUnits = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->sum('quantity');
        $totalSqm = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->sum('sqm');
        $overallUnits = InventoryItem::sum('quantity');
        $totalModels = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->count();
        $totalManufacturers = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->distinct('manufacturer')->count('manufacturer');
        $lowStockCount = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->where('quantity', '<=', 5)->count();

        $globaltronicsUnits = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->where('location', 'Globaltronics')->sum('quantity');
        $ajuanUnits = InventoryItem::when($category && $category !== 'all', $categoryFilterCallback)->where('location', 'AJUAN')->sum('quantity');

        $standardManufacturers = [
            'UNILUMIN',
            'FABULUX',
            'DAHUA',
            'LEDTOP',
            'ABSEN',
            'UNIVIEW',
            'LIGHTKING',
            'LEYARD',
            'DAHUA TECH',
            'LINSO',
            'SHANGHAI',
            'SHENZEN',
            'SAMSUNG',
            'TRT',
            'GLOBALTRONICS',
            'PHILIPS',
            'LKGT - INDOOR LED DISPLAY',
            'LKGT',
            'NOVASTAR',
            'COLORLIGHT',
            'AVER',
            'SHUTTLE',
        ];
        $manufacturersList = collect($standardManufacturers)
            ->merge(InventoryItem::select('manufacturer')->distinct()->pluck('manufacturer')->map(fn ($m) => strtoupper($m)))
            ->unique()
            ->values();

        $standardCategories = [
            'CENTRALIZED LED INVENTORY',
            'EOL PHILIPS UNITS',
            'Service Units (Events, Demo)',
            'LED Service Units',
            'Philips Service Units',
            'Video Controllers / Processors',
            'Shuttle',
            'Aver',
            'Digital iPoster',
            'Kiosks',
        ];
        $categories = collect($standardCategories)
            ->merge(InventoryItem::select('category')->distinct()->pluck('category'))
            ->unique()
            ->values();

        $standardLocations = [
            'MARIKINA',
            'GLOBALTRONICS',
            'AJUAN - 1ST FLR',
            'A JUAN MAIN 1ST FLOOR',
            '2ND FLR OCAP',
        ];
        $availableLocations = collect($standardLocations)
            ->merge(InventoryItem::select('location')->distinct()->pluck('location'))
            ->unique()
            ->values();
        $screenSizes = InventoryItem::select('screen_size')->whereNotNull('screen_size')->distinct()->pluck('screen_size');

        $ledCount = InventoryItem::where('category', 'CENTRALIZED LED INVENTORY')->count();
        $philipsCount = InventoryItem::where('category', 'EOL PHILIPS UNITS')->count();
        $serviceUnitsCount = InventoryItem::where(function ($q) use ($serviceUnitSubCategories) {
            $q->whereIn('category', array_merge($serviceUnitSubCategories, ['Service Units (Events, Demo)', 'SERVICE UNITS (EVENTS, DEMO)', 'Service Units', 'SERVICE UNITS']))
                ->orWhere('category', 'like', '%Service Unit%')
                ->orWhere('category', 'like', '%SERVICE UNIT%');
        })->count();

        $subCategoryCounts = [];
        foreach ($serviceUnitSubCategories as $sub) {
            $subCategoryCounts[$sub] = InventoryItem::where('category', $sub)->count();
        }

        return view('admin.inventory.index', [
            'items' => $items,
            'search' => $search,
            'selectedCategory' => $category,
            'selectedLocation' => $location,
            'selectedScreenSize' => $screenSize,
            'selectedManufacturer' => $manufacturer,
            'selectedStatus' => $status,
            'totalUnits' => $totalUnits,
            'totalSqm' => $totalSqm,
            'overallUnits' => $overallUnits,
            'totalModels' => $totalModels,
            'totalManufacturers' => $totalManufacturers,
            'lowStockCount' => $lowStockCount,
            'globaltronicsUnits' => $globaltronicsUnits,
            'ajuanUnits' => $ajuanUnits,
            'categories' => $categories,
            'manufacturers' => $manufacturersList,
            'manufacturersList' => $manufacturersList,
            'availableLocations' => $availableLocations,
            'screenSizes' => $screenSizes,
            'ledCount' => $ledCount,
            'philipsCount' => $philipsCount,
            'serviceUnitsCount' => $serviceUnitsCount,
            'serviceUnitSubCategories' => $serviceUnitSubCategories,
            'subCategoryCounts' => $subCategoryCounts,
        ]);
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(Request $request): RedirectResponse
    {
        $allowedLocations = [
            'MARIKINA',
            'GLOBALTRONICS',
            'AJUAN - 1ST FLR',
            'A JUAN MAIN 1ST FLOOR',
            'AJUAN MAIN 1ST FLOOR',
            'A JUAN MAIN 3RD FLOOR',
            '2ND FLR OCAP',
            '2ND FLOOR OCAP',
            'CLARK OFFICE',
            'DEFECTIVE',
            'Globaltronics',
            'AJUAN',
        ];

        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:255'],
            'tag_number' => ['nullable', 'string', 'max:5000'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'check_in_date' => ['required', 'date'],
            'model' => ['required', 'string', 'max:255'],
            'screen_size' => ['nullable', 'string', 'max:50'],
            'item_description' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'original_quantity' => ['nullable', 'integer', 'min:0'],
            'acu_quantity' => ['nullable', 'integer', 'min:0'],
            'forecasted_quantity' => ['nullable', 'integer', 'min:0'],
            'sqm' => ['nullable', 'numeric', 'min:0'],
            'location' => ['required', 'string', Rule::in($allowedLocations)],
            'status' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'reservation_qty' => ['nullable', 'integer', 'min:0'],
            'reservation_project' => ['nullable', 'string', 'max:255'],
            'reservation_remarks' => ['nullable', 'string', 'max:1000'],
            'history_qty' => ['nullable', 'integer', 'min:0'],
            'history_project' => ['nullable', 'string', 'max:255'],
            'status_qty' => ['nullable', 'integer', 'min:0'],
            'status_particular' => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = 'CENTRALIZED LED INVENTORY';
        }

        if (empty($validated['status'])) {
            $validated['status'] = $validated['quantity'] <= 3 ? 'low_stock' : 'in_stock';
        }

        if (empty($validated['original_quantity'])) {
            $validated['original_quantity'] = $validated['quantity'];
        }

        if (empty($validated['remarks'])) {
            $validated['remarks'] = 'New shipment in good condition';
        }

        // Initialize default movement history
        $validated['movement_history'] = [
            [
                'date' => ($validated['check_in_date'] ?? date('Y-m-d')) . ' 09:30',
                'action' => 'RECEIVED: Received initial batch of ' . $validated['quantity'] . ' pcs',
            ],
        ];

        // Auto-extract screen size if not provided (e.g. from 55" or description)
        if (empty($validated['screen_size']) && preg_match('/(\d+(?:\.\d+)?)\s*"/i', $validated['item_description'], $matches)) {
            $validated['screen_size'] = $matches[1].'"';
        }

        $validated['created_by'] = Auth::id();

        $item = InventoryItem::create($validated);

        return redirect()->route('admin.inventory.index', ['category' => $item->category])
            ->with('status', "Inventory unit '{$item->model}' ({$item->manufacturer}) added successfully to {$item->location}.");
    }

    /**
     * Update the specified inventory item.
     */
    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $allowedLocations = [
            'MARIKINA',
            'GLOBALTRONICS',
            'AJUAN - 1ST FLR',
            'A JUAN MAIN 1ST FLOOR',
            'AJUAN MAIN 1ST FLOOR',
            'A JUAN MAIN 3RD FLOOR',
            '2ND FLR OCAP',
            '2ND FLOOR OCAP',
            'CLARK OFFICE',
            'DEFECTIVE',
            'Globaltronics',
            'AJUAN',
        ];

        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:255'],
            'tag_number' => ['nullable', 'string', 'max:5000'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'check_in_date' => ['required', 'date'],
            'model' => ['required', 'string', 'max:255'],
            'screen_size' => ['nullable', 'string', 'max:50'],
            'item_description' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'original_quantity' => ['nullable', 'integer', 'min:0'],
            'acu_quantity' => ['nullable', 'integer', 'min:0'],
            'forecasted_quantity' => ['nullable', 'integer', 'min:0'],
            'sqm' => ['nullable', 'numeric', 'min:0'],
            'location' => ['required', 'string', Rule::in($allowedLocations)],
            'status' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'reservation_qty' => ['nullable', 'integer', 'min:0'],
            'reservation_project' => ['nullable', 'string', 'max:255'],
            'reservation_remarks' => ['nullable', 'string', 'max:1000'],
            'history_qty' => ['nullable', 'integer', 'min:0'],
            'history_project' => ['nullable', 'string', 'max:255'],
            'status_qty' => ['nullable', 'integer', 'min:0'],
            'status_particular' => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = $item->category ?? 'CENTRALIZED LED INVENTORY';
        }

        if (empty($validated['status'])) {
            $validated['status'] = $validated['quantity'] <= 3 ? 'low_stock' : 'in_stock';
        }

        if (empty($validated['screen_size']) && preg_match('/(\d+(?:\.\d+)?)\s*"/i', $validated['item_description'], $matches)) {
            $validated['screen_size'] = $matches[1].'"';
        }

        // If quantity changed, record in movement history
        if ((int)$validated['quantity'] !== (int)$item->quantity) {
            $history = is_array($item->movement_history) ? $item->movement_history : [];
            $history[] = [
                'date' => date('Y-m-d H:i'),
                'action' => 'ADJUSTMENT: Quantity updated from ' . $item->quantity . ' to ' . $validated['quantity'] . ' pcs',
            ];
            $validated['movement_history'] = $history;
        }

        $item->update($validated);

        return redirect()->route('admin.inventory.index', ['category' => $item->category])
            ->with('status', "Inventory unit '{$item->model}' updated successfully.");
    }

    /**
     * Add project reservation to the specified inventory item.
     */
    public function reserve(Request $request, InventoryItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_qty' => ['required', 'integer', 'min:1'],
            'reservation_project' => ['required', 'string', 'max:255'],
            'reservation_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $history = is_array($item->movement_history) ? $item->movement_history : [];
        $history[] = [
            'date' => date('Y-m-d H:i'),
            'action' => 'RESERVATION: Allocated ' . $validated['reservation_qty'] . ' pcs for ' . $validated['reservation_project'],
        ];

        $avail = max(0, (int)$item->quantity - (int)$validated['reservation_qty']);

        $item->update([
            'reservation_qty' => (int)($item->reservation_qty ?? 0) + (int)$validated['reservation_qty'],
            'reservation_project' => $validated['reservation_project'],
            'reservation_remarks' => $validated['reservation_remarks'] ?? $item->reservation_remarks,
            'forecasted_quantity' => $avail,
            'movement_history' => $history,
        ]);

        return redirect()->route('admin.inventory.index', ['category' => $item->category])
            ->with('status', "Reserved {$validated['reservation_qty']} pcs of '{$item->model}' for {$validated['reservation_project']}.");
    }

    /**
     * Remove the specified inventory item from stock.
     */
    public function destroy(InventoryItem $item): RedirectResponse
    {
        $model = $item->model;
        $category = $item->category;
        $item->delete();

        return redirect()->route('admin.inventory.index', ['category' => $category])
            ->with('status', "Inventory unit '{$model}' deleted from warehouse records.");
    }

    /**
     * Export inventory items to CSV dynamically formatted for the chosen category.
     */
    public function export(Request $request): StreamedResponse
    {
        $category = $request->query('category', 'CENTRALIZED LED INVENTORY');
        $location = $request->query('location');
        $search = $request->query('search');

        $query = InventoryItem::orderBy('id', 'asc');

        if ($category && $category !== 'all') {
            $isServiceUnitsParent = in_array($category, ['Service Units (Events, Demo)', 'SERVICE UNITS (EVENTS, DEMO)', 'Service Units', 'SERVICE UNITS']);
            if ($isServiceUnitsParent) {
                $query->where('category', 'like', '%Service Unit%');
            } else {
                $query->where('category', $category);
            }
        }

        if ($location && $location !== 'all') {
            $query->where('location', $location);
        }

        if ($search) {
            $query->search($search);
        }

        $items = $query->get();

        $filename = 'inventory_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $category ?: 'all')) . '_' . date('Y-m-d_His') . '.csv';

        $isCentralLed = ($category === 'CENTRALIZED LED INVENTORY');
        $isPhilips = ($category === 'EOL PHILIPS UNITS');

        if ($isCentralLed) {
            $headers = [
                'TAG #',
                'DATE RECEIVED',
                'PO / SKU No.',
                'MANUFACTURER',
                'MODEL / PIXEL PITCH',
                'ITEM DESCRIPTION',
                'LOCATION',
                'ON-HAND',
                'TOTAL ON-HAND',
                'PER PANEL SQM',
                'TOTAL AVAILABLE SQM',
                'AVAILABLE QTY',
                'AVAILABLE SQM',
                'ORIGINAL QTY',
                'RESERVATION QTY',
                'RESERVATION PROJECT',
                'RESERVATION REMARKS',
                'STATUS',
                'REMARKS',
            ];
        } elseif ($isPhilips) {
            $headers = [
                'TAG #',
                'DATE RECEIVED',
                'PO / SKU No.',
                'MANUFACTURER',
                'MODEL',
                'ITEM DESCRIPTION',
                'LOCATION',
                'SCREEN SIZE',
                'QUANTITY',
                'STATUS',
                'REMARKS',
            ];
        } else {
            $headers = [
                'TAG #',
                'DATE RECEIVED',
                'PO / SKU No.',
                'CATEGORY',
                'MANUFACTURER',
                'MODEL',
                'ITEM DESCRIPTION',
                'LOCATION',
                'QUANTITY',
                'STATUS',
                'REMARKS',
            ];
        }

        return response()->streamDownload(function () use ($headers, $items, $isCentralLed, $isPhilips) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($items as $item) {
                $dateRec = $item->check_in_date ? $item->check_in_date->format('Y-m-d') : '';

                if ($isCentralLed) {
                    $divisor = ($item->acu_quantity ?: $item->quantity) ?: 1;
                    $perPanelSqm = ($item->sqm && $divisor > 0) ? round($item->sqm / $divisor, 4) : 0;
                    $availQty = $item->forecasted_quantity !== null ? $item->forecasted_quantity : max(0, (int)$item->quantity - (int)($item->reservation_qty ?? 0));
                    $availSqm = $perPanelSqm > 0 ? round($availQty * $perPanelSqm, 2) : ($item->sqm ?? 0);

                    $row = [
                        $item->tag_number ?? '',
                        $dateRec,
                        $item->po_number ?? '',
                        $item->manufacturer ?? '',
                        $item->model ?? '',
                        $item->item_description ?? '',
                        $item->location ?? '',
                        $item->quantity ?? 0,
                        $item->acu_quantity ?? $item->quantity,
                        $perPanelSqm > 0 ? $perPanelSqm : '',
                        $item->sqm ?? '',
                        $availQty,
                        $availSqm > 0 ? $availSqm : '',
                        $item->original_quantity ?? '',
                        $item->reservation_qty ?? 0,
                        $item->reservation_project ?? '',
                        $item->reservation_remarks ?? '',
                        $item->status ?? '',
                        $item->remarks ?? '',
                    ];
                } elseif ($isPhilips) {
                    $row = [
                        $item->tag_number ?? '',
                        $dateRec,
                        $item->po_number ?? '',
                        $item->manufacturer ?? '',
                        $item->model ?? '',
                        $item->item_description ?? '',
                        $item->location ?? '',
                        $item->screen_size ?? '',
                        $item->quantity ?? 0,
                        $item->status ?? '',
                        $item->remarks ?? '',
                    ];
                } else {
                    $row = [
                        $item->tag_number ?? '',
                        $dateRec,
                        $item->po_number ?? '',
                        $item->category ?? '',
                        $item->manufacturer ?? '',
                        $item->model ?? '',
                        $item->item_description ?? '',
                        $item->location ?? '',
                        $item->quantity ?? 0,
                        $item->status ?? '',
                        $item->remarks ?? '',
                    ];
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Download a sample CSV template for the selected category.
     */
    public function sampleCsv(Request $request): StreamedResponse
    {
        $category = $request->query('category', 'CENTRALIZED LED INVENTORY');
        $filename = 'sample_template_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $category)) . '.csv';

        $isCentralLed = ($category === 'CENTRALIZED LED INVENTORY');
        $isPhilips = ($category === 'EOL PHILIPS UNITS');

        if ($isCentralLed) {
            $headers = ['TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'MANUFACTURER', 'MODEL / PIXEL PITCH', 'ITEM DESCRIPTION', 'LOCATION', 'ON-HAND', 'TOTAL ON-HAND', 'PER PANEL SQM', 'TOTAL AVAILABLE SQM', 'ORIGINAL QTY', 'RESERVATION QTY', 'RESERVATION PROJECT', 'REMARKS'];
            $sampleRow = ['TAG-LED-001', date('Y-m-d'), 'PO-2026-081', 'UNILUMIN', 'Upad IV P2.6', 'UNILUMIN UPAD IV P2.6 INDOOR 500X500MM DIE CAST CABINET', 'Globaltronics', 150, 150, 0.25, 37.5, 150, 0, '', 'New delivery batch'];
        } elseif ($isPhilips) {
            $headers = ['TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'MANUFACTURER', 'MODEL', 'ITEM DESCRIPTION', 'LOCATION', 'SCREEN SIZE', 'QUANTITY', 'STATUS', 'REMARKS'];
            $sampleRow = ['TAG-PHI-001', date('Y-m-d'), 'PO-2026-042', 'PHILIPS', '55BDL4050D', 'PHILIPS 55INCH COMMERCIAL DISPLAY SLIM BEZEL', 'Globaltronics', '55"', 12, 'in_stock', 'Ready for deployment'];
        } else {
            $headers = ['TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'CATEGORY', 'MANUFACTURER', 'MODEL', 'ITEM DESCRIPTION', 'LOCATION', 'QUANTITY', 'STATUS', 'REMARKS'];
            $sampleRow = ['TAG-SU-001', date('Y-m-d'), 'PO-2026-015', $category, 'NOVASTAR', 'VX1000', 'NOVASTAR ALL-IN-ONE VIDEO PROCESSOR CONTROLLER', 'Globaltronics', 5, 'in_stock', 'Event demo unit'];
        }

        return response()->streamDownload(function () use ($headers, $sampleRow) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers);
            fputcsv($handle, $sampleRow);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Import inventory items from a CSV file mapped dynamically for the category.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'category' => ['required', 'string'],
        ]);

        $category = $request->input('category');
        $file = $request->file('file');

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['file' => 'Could not open the uploaded CSV file.']);
        }

        // Read and strip BOM if present
        $rawHeaders = fgetcsv($handle);
        if (! $rawHeaders) {
            fclose($handle);
            return back()->withErrors(['file' => 'The uploaded CSV file is empty.']);
        }

        $headerMap = [];
        foreach ($rawHeaders as $index => $h) {
            $clean = strtolower(trim(preg_replace('/[\x{FEFF}\x{200B}]/u', '', $h)));
            $clean = preg_replace('/[^a-z0-9]/', '', $clean);
            $headerMap[$index] = $clean;
        }

        $importedCount = 0;
        $updatedCount = 0;
        $allowedLocations = ['Globaltronics', 'AJUAN', 'DEFECTIVE', 'SHOWROOM', 'OTHER'];

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue; // Skip empty rows
            }

            $data = [];
            foreach ($row as $idx => $val) {
                $colKey = $headerMap[$idx] ?? null;
                if (! $colKey) continue;
                $val = trim($val);

                if (in_array($colKey, ['tag', 'tagno', 'tagnumber', 'tagid'])) {
                    $data['tag_number'] = $val;
                } elseif (in_array($colKey, ['datereceived', 'date', 'checkindate'])) {
                    $data['check_in_date'] = $val;
                } elseif (in_array($colKey, ['poskuno', 'pono', 'ponumber', 'po', 'sku', 'skuno'])) {
                    $data['po_number'] = $val;
                } elseif (in_array($colKey, ['manufacturer', 'mfr', 'brand'])) {
                    $data['manufacturer'] = $val;
                } elseif (in_array($colKey, ['modelpixelpitch', 'model', 'pixelpitch'])) {
                    $data['model'] = $val;
                } elseif (in_array($colKey, ['itemdescription', 'description', 'itemname', 'name'])) {
                    $data['item_description'] = $val;
                } elseif (in_array($colKey, ['location', 'warehouse', 'facility'])) {
                    $data['location'] = $val;
                } elseif (in_array($colKey, ['onhand', 'quantity', 'qty'])) {
                    $data['quantity'] = (int) $val;
                } elseif (in_array($colKey, ['totalonhand', 'acuquantity', 'acuqty'])) {
                    $data['acu_quantity'] = (int) $val;
                } elseif (in_array($colKey, ['totalavailablesqm', 'sqm', 'totalsqm'])) {
                    $data['sqm'] = (float) $val;
                } elseif (in_array($colKey, ['originalqty', 'originalquantity'])) {
                    $data['original_quantity'] = (int) $val;
                } elseif (in_array($colKey, ['screensize', 'size'])) {
                    $data['screen_size'] = $val;
                } elseif (in_array($colKey, ['status'])) {
                    $data['status'] = $val;
                } elseif (in_array($colKey, ['remarks', 'notes', 'particulars'])) {
                    $data['remarks'] = $val;
                } elseif (in_array($colKey, ['reservationqty', 'reservedqty'])) {
                    $data['reservation_qty'] = (int) $val;
                } elseif (in_array($colKey, ['reservationproject', 'reservedproject', 'project'])) {
                    $data['reservation_project'] = $val;
                } elseif (in_array($colKey, ['reservationremarks', 'reservedremarks'])) {
                    $data['reservation_remarks'] = $val;
                } elseif (in_array($colKey, ['category'])) {
                    $data['category'] = $val;
                }
            }

            if (empty($data['model']) && empty($data['item_description'])) {
                continue;
            }

            if (empty($data['model'])) {
                $data['model'] = substr($data['item_description'], 0, 50);
            }
            if (empty($data['item_description'])) {
                $data['item_description'] = $data['model'];
            }
            if (empty($data['manufacturer'])) {
                $data['manufacturer'] = 'UNSPECIFIED';
            }
            if (empty($data['location']) || ! in_array($data['location'], $allowedLocations)) {
                $data['location'] = 'Globaltronics';
            }
            if (! isset($data['quantity'])) {
                $data['quantity'] = 0;
            }

            $data['category'] = ! empty($data['category']) ? $data['category'] : $category;

            // Format check-in date
            if (! empty($data['check_in_date'])) {
                $parsedDate = strtotime($data['check_in_date']);
                $data['check_in_date'] = $parsedDate ? date('Y-m-d', $parsedDate) : date('Y-m-d');
            } else {
                $data['check_in_date'] = date('Y-m-d');
            }

            // Screen size auto extraction
            if (empty($data['screen_size']) && preg_match('/(\d+(?:\.\d+)?)\s*"/i', $data['item_description'], $m)) {
                $data['screen_size'] = $m[1].'"';
            }

            $data['created_by'] = Auth::id() ?: 1;

            if (! empty($data['tag_number'])) {
                InventoryItem::updateOrCreate(
                    ['tag_number' => $data['tag_number']],
                    $data
                );
                $updatedCount++;
            } else {
                InventoryItem::create($data);
                $importedCount++;
            }
        }

        fclose($handle);

        $msg = "Import complete for {$category}: {$importedCount} new items created";
        if ($updatedCount > 0) {
            $msg .= ", {$updatedCount} existing items updated by Tag #";
        }
        $msg .= '.';

        return redirect()->route('admin.inventory.index', ['category' => $category])
            ->with('status', $msg);
    }
}
