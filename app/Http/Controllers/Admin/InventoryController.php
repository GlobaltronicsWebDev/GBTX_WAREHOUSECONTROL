<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
            'forecasted_quantity' => ['nullable', 'integer'],
            'sqm' => ['nullable', 'numeric'],
            'location' => ['required', 'string', 'max:255'],
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
            'forecasted_quantity' => ['nullable', 'integer'],
            'sqm' => ['nullable', 'numeric'],
            'location' => ['required', 'string', 'max:255'],
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

        @set_time_limit(300);

        try {
            $handle = fopen($file->getRealPath(), 'r');
            if (! $handle) {
                return back()->withErrors(['file' => 'Could not open the uploaded CSV file.']);
            }

            // Read all rows into memory for structure detection
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);

            if (empty($rows)) {
                return back()->withErrors(['file' => 'The uploaded CSV file is empty.']);
            }

            // Inspect available columns in inventory_items table
            $tableColumns = Schema::getColumnListing('inventory_items');
            $hasColumn = fn(string $col) => in_array($col, $tableColumns, true);

            // Safe user ID for foreign key constraint
            $creatorId = Auth::id() ?: (User::value('id') ?: null);

            // Attempt to expand reservation_project and history_project to TEXT on MySQL
            try {
                DB::statement('ALTER TABLE inventory_items MODIFY COLUMN reservation_project TEXT NULL');
                DB::statement('ALTER TABLE inventory_items MODIFY COLUMN history_project TEXT NULL');
            } catch (\Throwable $ignored) {
            }

            // Detect if reservation_project column is TEXT or VARCHAR
            $isTextCol = false;
            try {
                $colType = Schema::getColumnType('inventory_items', 'reservation_project');
                if (in_array(strtolower((string)$colType), ['text', 'mediumtext', 'longtext'])) {
                    $isTextCol = true;
                }
            } catch (\Throwable $e) {
                try {
                    $colType = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = 'inventory_items' AND COLUMN_NAME = 'reservation_project'");
                    if ($colType && in_array(strtolower($colType->DATA_TYPE), ['text', 'mediumtext', 'longtext'])) {
                        $isTextCol = true;
                    }
                } catch (\Throwable $ignored) {
                }
            }

            // Helper for numbers (handles commas like "2,888", units like "500 pcs", "-242 pcs", "101.25 sqm", "-12.39 sqm", etc.)
            $parseNum = function ($val, $isFloat = false) {
                if ($val === null || $val === '') return null;
                $val = trim((string)$val);
                if ($val === '' || $val === '—' || $val === '-') return null;
                $cleaned = preg_replace('/[^\d\.\-]/', '', $val);
                if ($cleaned === '' || $cleaned === '-') return null;
                return $isFloat ? (float)$cleaned : (int)$cleaned;
            };

            $cleanStr = function ($val) {
                if ($val === null) return '';
                $val = preg_replace('/[\x{FEFF}\x{200B}]/u', '', (string)$val);
                return trim(preg_replace('/\s+/', ' ', $val));
            };

            // 1. Detect Header Row 1 (skips any pre-header titles or blank rows)
            $headerRow1Idx = null;
            $headerRow2Idx = null;

            foreach ($rows as $idx => $r) {
                $joined = strtolower(implode(' ', $r));
                $score = 0;
                foreach (['tag', 'date', 'po', 'sku', 'manufacturer', 'model', 'description', 'location', 'inventory', 'on-hand'] as $kw) {
                    if (str_contains($joined, $kw)) $score++;
                }
                if ($score >= 3) {
                    $headerRow1Idx = $idx;
                    break;
                }
            }

            if ($headerRow1Idx === null) {
                $headerRow1Idx = 0;
            }

            // 2. Check if the next row is a secondary subheader row (2-tier header)
            if (isset($rows[$headerRow1Idx + 1])) {
                $nextJoined = strtolower(implode(' ', $rows[$headerRow1Idx + 1]));
                $subScore = 0;
                foreach (['on-hand', 'total', 'sqm', 'panel', 'qty', 'particular', 'project'] as $kw) {
                    if (str_contains($nextJoined, $kw)) $subScore++;
                }
                if ($subScore >= 2) {
                    $headerRow2Idx = $headerRow1Idx + 1;
                }
            }

            // 3. Build Column Map from Header 1 and Header 2
            $columnMap = [];
            $h1 = $rows[$headerRow1Idx];
            $h2 = $headerRow2Idx !== null ? $rows[$headerRow2Idx] : [];

            $currentParent = '';
            foreach ($h1 as $colIdx => $colVal) {
                $colClean = preg_replace('/[^a-z0-9]/', '', strtolower((string)$colVal));
                if (!empty($colClean)) {
                    $currentParent = $colClean;
                }

                $subVal = isset($h2[$colIdx]) ? preg_replace('/[^a-z0-9]/', '', strtolower((string)$h2[$colIdx])) : '';
                $colKey = null;

                if (in_array($colClean, ['tag', 'tagno', 'tagnumber', 'tagid']) || str_starts_with($colClean, 'tag')) {
                    $colKey = 'tag_number';
                } elseif (in_array($colClean, ['datereceived', 'date', 'checkindate'])) {
                    $colKey = 'check_in_date';
                } elseif (str_contains($colClean, 'po') || str_contains($colClean, 'sku')) {
                    $colKey = 'po_number';
                } elseif (str_contains($colClean, 'manufacturer') || str_contains($colClean, 'brand') || str_contains($colClean, 'mfr')) {
                    $colKey = 'manufacturer';
                } elseif (str_contains($colClean, 'model') || str_contains($colClean, 'pixelpitch')) {
                    $colKey = 'model';
                } elseif (str_contains($colClean, 'description') || str_contains($colClean, 'item')) {
                    $colKey = 'item_description';
                } elseif (str_contains($colClean, 'location') || str_contains($colClean, 'warehouse') || str_contains($colClean, 'facility')) {
                    $colKey = 'location';
                } elseif (str_contains($currentParent, 'inventory')) {
                    if (str_contains($subVal, 'sqm')) {
                        if (str_contains($subVal, 'perpanel') || str_contains($subVal, 'panel')) {
                            $colKey = 'per_panel_sqm';
                        } else {
                            $colKey = 'sqm';
                        }
                    } elseif (str_contains($subVal, 'total') || str_contains($subVal, 'acu')) {
                        $colKey = 'acu_quantity';
                    } elseif (str_contains($subVal, 'onhand') || str_contains($subVal, 'qty')) {
                        $colKey = 'quantity';
                    }
                } elseif (str_contains($currentParent, 'available') || str_contains($colClean, 'available')) {
                    if (str_contains($subVal, 'sqm') || str_contains($colClean, 'availablesqm')) {
                        $colKey = 'available_sqm';
                    } else {
                        $colKey = 'forecasted_quantity';
                    }
                } elseif (str_contains($currentParent, 'reservation')) {
                    if (str_contains($subVal, 'project') || str_contains($subVal, 'detail')) {
                        $colKey = 'reservation_project';
                    } else {
                        $colKey = 'reservation_qty';
                    }
                } elseif (str_contains($currentParent, 'history')) {
                    if (str_contains($subVal, 'project')) {
                        $colKey = 'history_project';
                    } else {
                        $colKey = 'history_qty';
                    }
                } elseif (str_contains($currentParent, 'original') || str_contains($colClean, 'original')) {
                    $colKey = 'original_quantity';
                } elseif (str_contains($currentParent, 'status') || str_contains($colClean, 'status')) {
                    if (str_contains($subVal, 'particular')) {
                        $colKey = 'status_particular';
                    } elseif (str_contains($subVal, 'qty')) {
                        $colKey = 'status_qty';
                    } else {
                        $colKey = 'status_qty';
                    }
                } elseif (str_contains($colClean, 'screensize') || str_contains($colClean, 'size')) {
                    $colKey = 'screen_size';
                } elseif (str_contains($colClean, 'category')) {
                    $colKey = 'category';
                } elseif (str_contains($colClean, 'remark') || str_contains($colClean, 'note')) {
                    $colKey = 'remarks';
                }

                // Fallbacks based directly on subVal if header was empty
                if (!$colKey && !empty($subVal)) {
                    if ($subVal === 'onhand') $colKey = 'quantity';
                    elseif (str_contains($subVal, 'totalonhand')) $colKey = 'acu_quantity';
                    elseif (str_contains($subVal, 'totalsqm') || str_contains($subVal, 'totalavailablesqm')) $colKey = 'sqm';
                    elseif (str_contains($subVal, 'perpanelsqm')) $colKey = 'per_panel_sqm';
                    elseif ($subVal === 'qty') $colKey = 'quantity';
                    elseif ($subVal === 'sqm') $colKey = 'sqm';
                }

                $columnMap[$colIdx] = $colKey;
            }

            $startDataIdx = ($headerRow2Idx ?? $headerRow1Idx) + 1;
            $importedCount = 0;
            $updatedCount = 0;
            $lastItemModel = null;

            for ($i = $startDataIdx; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) {
                    continue; // Skip empty rows
                }

                // Check if summary row
                $rowJoined = strtolower(implode(' ', $row));
                if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) {
                    continue;
                }

                $rowData = [];
                foreach ($row as $cIdx => $rawVal) {
                    $key = $columnMap[$cIdx] ?? null;
                    if ($key) {
                        $rowData[$key] = $rawVal;
                    }
                }

                $model = $cleanStr($rowData['model'] ?? '');
                $desc = $cleanStr($rowData['item_description'] ?? '');
                $po = $cleanStr($rowData['po_number'] ?? '');
                $mfg = $cleanStr($rowData['manufacturer'] ?? '');
                $location = $cleanStr($rowData['location'] ?? '');

                $onHand = $parseNum($rowData['quantity'] ?? null);
                $totalOnHand = $parseNum($rowData['acu_quantity'] ?? null);
                $sqm = $parseNum($rowData['sqm'] ?? null, true);
                $availQty = $parseNum($rowData['forecasted_quantity'] ?? null);
                $origQty = $parseNum($rowData['original_quantity'] ?? null);
                $resQty = $parseNum($rowData['reservation_qty'] ?? null);
                $resProject = $cleanStr($rowData['reservation_project'] ?? '');
                $histQty = $parseNum($rowData['history_qty'] ?? null);
                $histProject = $cleanStr($rowData['history_project'] ?? '');

                $statusQty = $parseNum($rowData['status_qty'] ?? null);
                $statusPart = $cleanStr($rowData['status_particular'] ?? '');

                // Secondary location / batch check: inherit model, description, manufacturer, PO if this row has location or stock
                if (empty($model) && empty($desc) && $lastItemModel !== null) {
                    if (!empty($location) || ($onHand !== null && $onHand > 0) || !empty($po)) {
                        $model = $lastItemModel->model;
                        $desc = $lastItemModel->item_description;
                        if (empty($mfg)) $mfg = $lastItemModel->manufacturer;
                        if (empty($location)) $location = $lastItemModel->location;
                        if (empty($po)) $po = $lastItemModel->po_number;
                    }
                }

                if (!empty($model) || !empty($desc)) {
                    $itemData = [
                        'category' => !empty($rowData['category']) ? $cleanStr($rowData['category']) : $category,
                        'tag_number' => $cleanStr($rowData['tag_number'] ?? ''),
                        'po_number' => substr($po, 0, 100),
                        'manufacturer' => substr($mfg ?: 'UNILUMIN', 0, 100),
                        'model' => substr($model ?: substr($desc, 0, 50), 0, 100),
                        'item_description' => $desc ?: $model,
                        'location' => substr($location ?: 'Globaltronics', 0, 100),
                        'quantity' => $onHand ?? 0,
                    ];

                    if ($hasColumn('acu_quantity')) {
                        $itemData['acu_quantity'] = $totalOnHand !== null ? $totalOnHand : $onHand;
                    }
                    if ($hasColumn('sqm')) {
                        $itemData['sqm'] = $sqm;
                    }
                    if ($hasColumn('forecasted_quantity')) {
                        $itemData['forecasted_quantity'] = $availQty;
                    }
                    if ($hasColumn('original_quantity')) {
                        $itemData['original_quantity'] = $origQty !== null ? $origQty : ($totalOnHand ?? $onHand);
                    }
                    if ($hasColumn('reservation_qty')) {
                        $itemData['reservation_qty'] = $resQty ?? 0;
                    }
                    if ($hasColumn('reservation_project')) {
                        $itemData['reservation_project'] = $isTextCol ? $resProject : mb_substr($resProject, 0, 240);
                    }
                    if ($hasColumn('reservation_remarks')) {
                        $itemData['reservation_remarks'] = $cleanStr($rowData['reservation_remarks'] ?? '');
                    }
                    if ($hasColumn('history_qty')) {
                        $itemData['history_qty'] = $histQty ?? 0;
                    }
                    if ($hasColumn('history_project')) {
                        $itemData['history_project'] = $isTextCol ? $histProject : mb_substr($histProject, 0, 240);
                    }
                    if ($hasColumn('status_qty')) {
                        $itemData['status_qty'] = $statusQty ?? 0;
                    }
                    if ($hasColumn('status_particular')) {
                        $itemData['status_particular'] = $statusPart ?: 'OK';
                    }
                    if ($hasColumn('screen_size')) {
                        $itemData['screen_size'] = substr($cleanStr($rowData['screen_size'] ?? ''), 0, 50);
                    }
                    if ($hasColumn('status')) {
                        $itemData['status'] = substr(!empty($rowData['status']) ? $cleanStr($rowData['status']) : 'in_stock', 0, 50);
                    }
                    if ($hasColumn('remarks')) {
                        $itemData['remarks'] = $cleanStr($rowData['remarks'] ?? '');
                    }
                    if ($hasColumn('created_by')) {
                        $itemData['created_by'] = $creatorId;
                    }

                    // Auto extract screen size if empty
                    if (empty($itemData['screen_size']) && $hasColumn('screen_size') && preg_match('/(\d+(?:\.\d+)?)\s*"/i', $itemData['item_description'], $m)) {
                        $itemData['screen_size'] = $m[1] . '"';
                    }

                    // Parse check-in date safely
                    $rawDate = $cleanStr($rowData['check_in_date'] ?? '');
                    $parsedDate = null;
                    if (!empty($rawDate)) {
                        if (preg_match('/(\d{1,4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,4})/', $rawDate, $dm)) {
                            $cleanDateStr = str_replace('.', '-', $dm[1]);
                            $time = strtotime($cleanDateStr);
                            if ($time) {
                                $parsedDate = date('Y-m-d', $time);
                            }
                        }
                    }
                    $itemData['check_in_date'] = $parsedDate ?: date('Y-m-d');

                    // Filter only existing database columns to prevent unknown column SQL errors
                    $safeData = array_intersect_key($itemData, array_flip($tableColumns));

                    // Match existing item accurately to avoid overwriting distinct products
                    $existing = null;
                    if (!empty($itemData['tag_number'])) {
                        $tQuery = InventoryItem::where('category', $itemData['category'])
                            ->where('tag_number', $itemData['tag_number']);
                        if (!empty($itemData['po_number'])) {
                            $tQuery->where('po_number', $itemData['po_number']);
                        }
                        $existing = $tQuery->first();
                    }

                    if (!$existing) {
                        $query = InventoryItem::where('category', $itemData['category'])
                            ->where('model', $itemData['model'])
                            ->where('location', $itemData['location']);
                        if (!empty($itemData['po_number'])) {
                            $query->where('po_number', $itemData['po_number']);
                        } else {
                            $query->where(function ($q) {
                                $q->whereNull('po_number')->orWhere('po_number', '');
                            });
                        }
                        if (!empty($itemData['item_description'])) {
                            $descPrefix = mb_substr($itemData['item_description'], 0, 45);
                            $query->where('item_description', 'like', $descPrefix . '%');
                        }
                        $existing = $query->first();
                    }

                    $initialRes = [];
                    if ($resQty || !empty($resProject)) {
                        $initialRes[] = [
                            'qty' => $resQty ?? 0,
                            'project' => $resProject,
                            'remarks' => $cleanStr($rowData['reservation_remarks'] ?? ''),
                        ];
                    }
                    $initialHist = [];
                    if ($histQty || !empty($histProject)) {
                        $initialHist[] = [
                            'qty' => $histQty ?? 0,
                            'project' => $histProject,
                        ];
                    }
                    if ($hasColumn('movement_history')) {
                        $itemData['movement_history'] = [
                            'reservations' => $initialRes,
                            'history' => $initialHist,
                            'locations' => [
                                ['location' => $itemData['location'], 'qty' => $itemData['quantity']]
                            ],
                        ];
                    }

                    if ($existing) {
                        $existing->update($safeData);
                        $lastItemModel = $existing;
                        $updatedCount++;
                    } else {
                        $lastItemModel = InventoryItem::create($safeData);
                        $importedCount++;
                    }
                } elseif ($lastItemModel !== null) {
                    // Attach multi-line reservation, project history, or sub-location to preceding parent item
                    $needsSave = false;
                    $movements = $lastItemModel->movement_history ?? [];
                    if (!isset($movements['reservations'])) $movements['reservations'] = [];
                    if (!isset($movements['history'])) $movements['history'] = [];
                    if (!isset($movements['locations'])) $movements['locations'] = [];

                    if ($hasColumn('reservation_qty') && ($resQty || !empty($resProject))) {
                        $lastItemModel->reservation_qty = ($lastItemModel->reservation_qty ?? 0) + ($resQty ?? 0);
                        if (!empty($resProject) && $hasColumn('reservation_project')) {
                            $existingProj = (string)($lastItemModel->reservation_project ?? '');
                            $entryText = ($resQty ? "({$resQty} pcs) " : "") . $resProject;
                            if ($existingProj === '') {
                                $combined = $entryText;
                            } elseif (!str_contains($existingProj, $resProject)) {
                                $combined = $existingProj . "\n\n• " . $entryText;
                            } else {
                                $combined = $existingProj;
                            }
                            $lastItemModel->reservation_project = $isTextCol ? $combined : mb_substr($combined, 0, 240);
                        }
                        $movements['reservations'][] = [
                            'qty' => $resQty ?? 0,
                            'project' => $resProject,
                            'remarks' => $cleanStr($rowData['reservation_remarks'] ?? ''),
                        ];
                        $needsSave = true;
                    }
                    if ($hasColumn('history_qty') && ($histQty || !empty($histProject))) {
                        $lastItemModel->history_qty = ($lastItemModel->history_qty ?? 0) + ($histQty ?? 0);
                        if (!empty($histProject) && $hasColumn('history_project')) {
                            $existingHist = (string)($lastItemModel->history_project ?? '');
                            $entryText = ($histQty ? "({$histQty} pcs) " : "") . $histProject;
                            if ($existingHist === '') {
                                $combinedHist = $entryText;
                            } elseif (!str_contains($existingHist, $histProject)) {
                                $combinedHist = $existingHist . "\n\n• " . $entryText;
                            } else {
                                $combinedHist = $existingHist;
                            }
                            $lastItemModel->history_project = $isTextCol ? $combinedHist : mb_substr($combinedHist, 0, 240);
                        }
                        $movements['history'][] = [
                            'qty' => $histQty ?? 0,
                            'project' => $histProject,
                        ];
                        $needsSave = true;
                    }
                    if (!empty($location) || ($onHand !== null && $onHand > 0)) {
                        $movements['locations'][] = [
                            'location' => $location ?: 'GLOBALTRONICS',
                            'qty' => $onHand ?? 0,
                        ];
                        $needsSave = true;
                    }

                    if ($hasColumn('movement_history')) {
                        $lastItemModel->movement_history = $movements;
                    }

                    if ($needsSave) {
                        $lastItemModel->save();
                    }
                }
            }

            $msg = "Import complete for {$category}: {$importedCount} new items created";
            if ($updatedCount > 0) {
                $msg .= ", {$updatedCount} existing items updated";
            }
            $msg .= '.';

            return redirect()->route('admin.inventory.index', ['category' => $category])
                ->with('status', $msg);

        } catch (\Throwable $e) {
            Log::error('Inventory CSV Import Exception: ' . $e->getMessage(), [
                'category' => $category,
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.inventory.index', ['category' => $category])
                ->withErrors(['file' => 'Import could not complete: ' . $e->getMessage()]);
        }
    }
}
