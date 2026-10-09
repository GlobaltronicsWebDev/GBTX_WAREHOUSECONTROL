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

        $applyCategoryScope = function ($q, string $targetCategory) use ($serviceUnitSubCategories) {
            $targetUpper = strtoupper(trim($targetCategory));
            $isServiceUnitsParent = in_array($targetUpper, [
                'SERVICE UNITS (EVENTS, DEMO)',
                'SERVICE UNITS',
                'SERVICE UNIT',
            ]);

            if ($isServiceUnitsParent) {
                $q->where(function ($sq) {
                    $sq->where('category', 'like', '%Service Unit%')
                        ->orWhere('category', 'like', '%SERVICE UNIT%')
                        ->orWhere('category', 'like', '%Service Units%')
                        ->orWhere('category', 'like', '%SERVICE UNITS%')
                        ->orWhereIn('category', [
                            'LED Service Units', 'LED SERVICES UNITS', 'LED SERVICE UNITS', 'LED Service Unit',
                            'Philips Service Units', 'PHILIPS SERVICE UNITS', 'Philips Service Unit', 'PHILIPS SERVICE UNIT',
                            'Video Controllers / Processors', 'VIDEO CONTROLLERS / PROCESSORS', 'Video Controllers', 'Video Processors',
                            'Shuttle', 'SHUTTLE',
                            'Aver', 'AVER',
                            'Digital iPoster', 'DIGITAL IPOSTER', 'iPoster', 'IPoster',
                            'Kiosks', 'KIOSKS', 'Kiosk', 'KIOSK',
                        ]);
                });
            } else {
                $variants = $this->getCategoryVariants($targetCategory);
                $q->where(function ($sq) use ($targetCategory, $variants) {
                    $sq->whereIn('category', $variants)
                        ->orWhereRaw('LOWER(category) = ?', [strtolower(trim($targetCategory))]);
                });
            }
        };

        if ($category && $category !== 'all') {
            $applyCategoryScope($query, $category);
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

        // Compute full query totals across all matching rows for sticky summary count footers
        $filterBaseQuery = clone $query;
        $filterBaseQuery->getQuery()->orders = null;

        $categoryTotalQty = (int) (clone $filterBaseQuery)->sum('quantity');
        $categoryTotalAcuQty = (int) (clone $filterBaseQuery)->sum(DB::raw('COALESCE(acu_quantity, quantity)'));
        $categoryTotalForecastedQty = (int) (clone $filterBaseQuery)->sum(DB::raw('COALESCE(forecasted_quantity, CASE WHEN quantity - COALESCE(reservation_qty, 0) > 0 THEN quantity - COALESCE(reservation_qty, 0) ELSE 0 END)'));
        $categoryTotalHistoryQty = (int) (clone $filterBaseQuery)->sum('history_qty');
        $categoryTotalReservedQty = (int) (clone $filterBaseQuery)->sum('reservation_qty');
        $categoryTotalStatusQty = (int) (clone $filterBaseQuery)->sum('status_qty');
        $categoryTotalOriginalQty = (int) (clone $filterBaseQuery)->sum(DB::raw('COALESCE(original_quantity, quantity)'));
        $categoryTotalSqm = (float) (clone $filterBaseQuery)->sum('sqm');

        // Limit to 10 items per page with next page navigation
        $perPage = (int) $request->query('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

        $categoryScopeCallback = function ($q) use ($applyCategoryScope, $category) {
            $applyCategoryScope($q, $category);
        };

        $totalUnits = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->sum('quantity');
        $totalSqm = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->sum('sqm');
        $overallUnits = InventoryItem::sum('quantity');
        $totalModels = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->count();
        $totalManufacturers = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->distinct('manufacturer')->count('manufacturer');
        $lowStockCount = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->where('quantity', '<=', 5)->count();

        $globaltronicsUnits = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->where('location', 'Globaltronics')->sum('quantity');
        $ajuanUnits = InventoryItem::when($category && $category !== 'all', $categoryScopeCallback)->where('location', 'AJUAN')->sum('quantity');

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
            'A-JUAN - 3RD FLR',
            'A JUAN - 2ND FLR',
            'AJUAN - 1ST FLR',
            'A JUAN MAIN 1ST FLOOR',
            '2ND FLR OCAP',
        ];
        $availableLocations = collect($standardLocations)
            ->merge(InventoryItem::select('location')->distinct()->pluck('location'))
            ->unique()
            ->values();
        $screenSizes = InventoryItem::select('screen_size')->whereNotNull('screen_size')->distinct()->pluck('screen_size');

        $ledVariants = $this->getCategoryVariants('CENTRALIZED LED INVENTORY');
        $ledCount = InventoryItem::where(function ($q) use ($ledVariants) {
            $q->whereIn('category', $ledVariants)
                ->orWhereRaw('LOWER(category) = ?', ['centralized led inventory']);
        })->count();
        $ledTotalUnits = (int) InventoryItem::where(function ($q) use ($ledVariants) {
            $q->whereIn('category', $ledVariants)
                ->orWhereRaw('LOWER(category) = ?', ['centralized led inventory']);
        })->sum('quantity');

        $philipsVariants = $this->getCategoryVariants('EOL PHILIPS UNITS');
        $philipsCount = InventoryItem::where(function ($q) use ($philipsVariants) {
            $q->whereIn('category', $philipsVariants)
                ->orWhereRaw('LOWER(category) = ?', ['eol philips units']);
        })->count();
        $philipsTotalUnits = (int) InventoryItem::where(function ($q) use ($philipsVariants) {
            $q->whereIn('category', $philipsVariants)
                ->orWhereRaw('LOWER(category) = ?', ['eol philips units']);
        })->sum('quantity');

        $serviceUnitsQuery = InventoryItem::where(function ($q) {
            $q->where('category', 'like', '%Service Unit%')
                ->orWhere('category', 'like', '%SERVICE UNIT%')
                ->orWhere('category', 'like', '%Service Units%')
                ->orWhere('category', 'like', '%SERVICE UNITS%')
                ->orWhereIn('category', [
                    'LED Service Units', 'LED SERVICES UNITS', 'LED SERVICE UNITS', 'LED Service Unit',
                    'Philips Service Units', 'PHILIPS SERVICE UNITS', 'Philips Service Unit', 'PHILIPS SERVICE UNIT',
                    'Video Controllers / Processors', 'VIDEO CONTROLLERS / PROCESSORS', 'Video Controllers', 'Video Processors',
                    'Shuttle', 'SHUTTLE',
                    'Aver', 'AVER',
                    'Digital iPoster', 'DIGITAL IPOSTER', 'iPoster', 'IPoster',
                    'Kiosks', 'KIOSKS', 'Kiosk', 'KIOSK',
                ]);
        });
        $serviceUnitsCount = (clone $serviceUnitsQuery)->count();
        $serviceUnitsTotalUnits = (int) (clone $serviceUnitsQuery)->sum('quantity');

        $subCategoryCounts = [];
        $subCategoryQuantities = [];
        foreach ($serviceUnitSubCategories as $sub) {
            $subVariants = $this->getCategoryVariants($sub);
            $subQ = InventoryItem::where(function ($q) use ($sub, $subVariants) {
                $q->whereIn('category', $subVariants)
                    ->orWhereRaw('LOWER(category) = ?', [strtolower(trim($sub))]);
            });
            $subCategoryCounts[$sub] = (clone $subQ)->count();
            $subCategoryQuantities[$sub] = (int) (clone $subQ)->sum('quantity');
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
            'ledTotalUnits' => $ledTotalUnits,
            'philipsCount' => $philipsCount,
            'philipsTotalUnits' => $philipsTotalUnits,
            'serviceUnitsCount' => $serviceUnitsCount,
            'serviceUnitsTotalUnits' => $serviceUnitsTotalUnits,
            'serviceUnitSubCategories' => $serviceUnitSubCategories,
            'subCategoryCounts' => $subCategoryCounts,
            'subCategoryQuantities' => $subCategoryQuantities,
            'categoryTotalQty' => $categoryTotalQty,
            'categoryTotalAcuQty' => $categoryTotalAcuQty,
            'categoryTotalForecastedQty' => $categoryTotalForecastedQty,
            'categoryTotalHistoryQty' => $categoryTotalHistoryQty,
            'categoryTotalReservedQty' => $categoryTotalReservedQty,
            'categoryTotalStatusQty' => $categoryTotalStatusQty,
            'categoryTotalOriginalQty' => $categoryTotalOriginalQty,
            'categoryTotalSqm' => $categoryTotalSqm,
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
    public function destroy(Request $request, $item): RedirectResponse
    {
        $inventoryItem = $item instanceof InventoryItem ? $item : InventoryItem::find($item);
        if (! $inventoryItem) {
            return redirect()->route('admin.inventory.index')
                ->with('status', 'Inventory unit has already been removed or does not exist.');
        }

        $model = $inventoryItem->model;
        $category = $inventoryItem->category;
        $inventoryItem->delete();

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
                'LOCATION',
                'DATE RECEIVED',
                'MANUFACTURER',
                'MODEL',
                'PO / SKU No.',
                'PARTICULAR',
                'SERIAL NO.',
                'QTY',
                'ACU. QTY',
                'AVAILABLE QTY',
                'RESERVATION QTY',
                'RESERVATION PROJECT',
                'HISTORY QTY',
                'HISTORY PROJECT',
                'ORIGINAL QTY',
                'UNFOUND QTY',
                'UNFOUND STATUS',
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
                    $availQty = $item->forecasted_quantity !== null ? $item->forecasted_quantity : max(0, (int)$item->quantity - (int)($item->reservation_qty ?? 0));
                    $row = [
                        $item->location ?? 'A JUAN - 2ND FLR',
                        $dateRec,
                        $item->manufacturer ?? 'PHILIPS',
                        $item->model ?? '',
                        $item->po_number ?? '',
                        $item->item_description ?? '',
                        $item->tag_number ?? '',
                        $item->quantity ?? 0,
                        $item->acu_quantity ?? $item->quantity,
                        $availQty,
                        $item->reservation_qty ?? 0,
                        $item->reservation_project ?? '',
                        $item->history_qty ?? 0,
                        $item->history_project ?? '',
                        $item->original_quantity ?? $item->quantity,
                        $item->status_qty ?? 0,
                        $item->status_particular ?? 'OK',
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
        $isPhilipsEol = in_array($category, ['EOL PHILIPS UNITS', 'EOL Philips Units'], true);
        $isPhilipsService = in_array($category, ['Philips Service Units', 'PHILIPS SERVICE UNITS'], true);
        $isLedService = in_array($category, ['LED Service Units', 'LED SERVICE UNITS'], true);

        if ($isCentralLed) {
            $headers = ['TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'MANUFACTURER', 'MODEL / PIXEL PITCH', 'ITEM DESCRIPTION', 'LOCATION', 'ON-HAND', 'TOTAL ON-HAND', 'PER PANEL SQM', 'TOTAL AVAILABLE SQM', 'ORIGINAL QTY', 'RESERVATION QTY', 'RESERVATION PROJECT', 'REMARKS'];
            $sampleRow = ['TAG-LED-001', date('Y-m-d'), 'PO-2026-081', 'UNILUMIN', 'Upad IV P2.6', 'UNILUMIN UPAD IV P2.6 INDOOR 500X500MM DIE CAST CABINET', 'Globaltronics', 150, 150, 0.25, 37.5, 150, 0, '', 'New delivery batch'];
        } elseif ($isPhilipsEol) {
            $headers = ['MANUFACTURER', 'CHECK IN DATE', 'MODEL', 'ITEM DESCRIPTION', 'QTY'];
            $sampleRow = ['PHILIPS', date('Y-m-d'), 'BDL3230QL/75', '31.5" PHILIPS FLAT WIDE MONITOR', 103];
        } elseif ($isPhilipsService) {
            $headers = [
                'LOCATION',
                'MANUFACTURER',
                'CHECK IN DATE',
                'MODEL',
                'PO / SKU No.',
                'ITEM DESCRIPTION',
                'SERIAL NO.',
                'QTY',
                'ACU. QTY',
                'FORECASTED QTY',
                'HISTORY QTY',
                'HISTORY PROJECT',
                'REMARKS',
            ];
            $sampleRow = [
                'MARIKINA',
                'PHILIPS',
                date('Y-m-d'),
                '10BDL4151T/00',
                'PO-2026-001',
                '10" PHILIPS TOUCH SCREEN MONITOR',
                'AU0B1521000146, AU0B1518000139',
                13,
                13,
                13,
                0,
                '',
                'Philips Service demo unit',
            ];
        } elseif ($isLedService) {
            $headers = [
                'CDX',
                'LOCATION',
                'MANUFACTURER',
                'CHECK IN DATE',
                'MODEL',
                'PO / SKU No.',
                'ITEM DESCRIPTION',
                'QTY',
                'ACU. QTY',
                'PER PANEL SQM',
                'TOTAL AVAILABLE SQM',
                'FORECASTED QTY',
                'FORECASTED SQM',
                'RESERVATION QTY',
                'RESERVATION PROJECT',
                'HISTORY QTY',
                'HISTORY PROJECT',
                'ORIGINAL QTY',
                'STATUS QTY',
                'STATUS PARTICULAR',
                'REMARKS',
            ];
            $sampleRow = [
                'CDX-01',
                'GLOBALTRONICS',
                'UNILUMIN',
                date('Y-m-d'),
                'Upad IV P2.6',
                'PO-2026-081',
                'UNILUMIN UPAD IV P2.6 INDOOR 500X500MM DIE CAST CABINET',
                150,
                150,
                0.25,
                37.5,
                150,
                37.5,
                0,
                '',
                0,
                '',
                150,
                0,
                'OK',
                'Service demo panel',
            ];
        } else {
            $headers = ['TAG #', 'DATE RECEIVED', 'PO / SKU No.', 'CATEGORY', 'MANUFACTURER', 'MODEL', 'ITEM DESCRIPTION', 'LOCATION', 'QTY', 'STATUS', 'REMARKS'];
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
                foreach (['tag', 'date', 'po', 'sku', 'manufacturer', 'model', 'description', 'location', 'inventory', 'on-hand', 'qty', 'particular'] as $kw) {
                    if (str_contains($joined, $kw)) $score++;
                }
                if ($score >= 2) {
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
                foreach (['on-hand', 'total', 'sqm', 'panel', 'qty', 'particular', 'project', 'serial', 'acu'] as $kw) {
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

                // 1. Tag / Serial Number
                if (in_array($colClean, ['tag', 'tagno', 'tagnumber', 'tagid', 'serial', 'serialno', 'serialnumber', 'sn', 'serials'])
                    || str_starts_with($colClean, 'tag')
                    || str_starts_with($colClean, 'serial')
                    || str_contains($subVal, 'serial')
                    || str_contains($subVal, 'tagno')
                ) {
                    $colKey = 'tag_number';

                // 2. Date
                } elseif (in_array($colClean, ['datereceived', 'date', 'checkindate', 'checkin', 'receiveddate', 'datecheckedin'])
                    || str_contains($colClean, 'date')
                ) {
                    $colKey = 'check_in_date';

                // 3. PO / SKU
                } elseif (str_contains($colClean, 'po') || str_contains($colClean, 'sku')) {
                    $colKey = 'po_number';

                // 4. Manufacturer
                } elseif (str_contains($colClean, 'manufacturer') || str_contains($colClean, 'brand') || str_contains($colClean, 'mfr')) {
                    $colKey = 'manufacturer';

                // 5. Model
                } elseif ((str_contains($colClean, 'model') || str_contains($colClean, 'pixelpitch') || str_contains($colClean, 'pitch')) && !str_contains($colClean, 'desc')) {
                    $colKey = 'model';

                // 6. Item Description / Particular
                } elseif (str_contains($colClean, 'description') || str_contains($colClean, 'item') || str_contains($colClean, 'particular') || str_contains($subVal, 'particular')) {
                    if (str_contains($subVal, 'serial')) {
                        $colKey = 'tag_number';
                    } else {
                        $colKey = 'item_description';
                    }

                // 7. Location
                } elseif (str_contains($colClean, 'location') || str_contains($colClean, 'warehouse') || str_contains($colClean, 'facility') || str_contains($colClean, 'storage')) {
                    $colKey = 'location';

                // 8. SQM fields
                } elseif (str_contains($colClean, 'perpanel') || str_contains($subVal, 'perpanel') || str_contains($colClean, 'panelsqm') || str_contains($subVal, 'panelsqm')) {
                    $colKey = 'per_panel_sqm';
                } elseif (str_contains($colClean, 'sqm') || str_contains($subVal, 'sqm')) {
                    if (str_contains($colClean, 'available') || str_contains($subVal, 'available')) {
                        $colKey = 'available_sqm';
                    } else {
                        $colKey = 'sqm';
                    }

                // 9. Acu. Quantity / Accumulated / Total On-Hand
                } elseif (str_contains($colClean, 'acu')
                    || str_contains($subVal, 'acu')
                    || str_contains($colClean, 'accumulat')
                    || str_contains($subVal, 'accumulat')
                    || str_contains($colClean, 'totalonhand')
                    || str_contains($subVal, 'totalonhand')
                    || (str_contains($currentParent, 'inventory') && (str_contains($subVal, 'total') || str_contains($subVal, 'acu')))
                ) {
                    $colKey = 'acu_quantity';

                // 10. Forecasted / Available QTY
                } elseif (str_contains($colClean, 'forecast')
                    || str_contains($subVal, 'forecast')
                    || str_contains($colClean, 'available')
                    || str_contains($currentParent, 'available')
                ) {
                    $colKey = 'forecasted_quantity';

                // 11. Reservation QTY / Project / Remarks
                } elseif (str_contains($currentParent, 'reservation') || str_contains($colClean, 'reservation') || str_contains($colClean, 'reserved')) {
                    if (str_contains($subVal, 'project') || str_contains($colClean, 'project') || str_contains($subVal, 'detail')) {
                        $colKey = 'reservation_project';
                    } elseif (str_contains($subVal, 'remark') || str_contains($colClean, 'remark')) {
                        $colKey = 'reservation_remarks';
                    } else {
                        $colKey = 'reservation_qty';
                    }

                // 12. History QTY / Project
                } elseif (str_contains($currentParent, 'history') || str_contains($colClean, 'history')) {
                    if (str_contains($subVal, 'project') || str_contains($colClean, 'project') || str_contains($subVal, 'detail')) {
                        $colKey = 'history_project';
                    } else {
                        $colKey = 'history_qty';
                    }

                // 13. Original Quantity
                } elseif (str_contains($currentParent, 'original') || str_contains($colClean, 'original') || str_contains($colClean, 'origqty')) {
                    $colKey = 'original_quantity';

                // 14. Status / Unfound / Damage
                } elseif (str_contains($currentParent, 'status') || str_contains($colClean, 'status') || str_contains($currentParent, 'unfound') || str_contains($colClean, 'unfound')) {
                    if (str_contains($subVal, 'particular') || str_contains($colClean, 'particular') || str_contains($subVal, 'status') || str_contains($subVal, 'remark')) {
                        $colKey = 'status_particular';
                    } else {
                        $colKey = 'status_qty';
                    }

                // 15. Screen Size
                } elseif (str_contains($colClean, 'screensize') || (str_contains($colClean, 'size') && !str_contains($colClean, 'pixel'))) {
                    $colKey = 'screen_size';

                // 16. Category
                } elseif (str_contains($colClean, 'category')) {
                    $colKey = 'category';

                // 17. Remarks
                } elseif (str_contains($colClean, 'remark') || str_contains($colClean, 'note') || str_contains($colClean, 'comment')) {
                    $colKey = 'remarks';

                // 18. MAIN QUANTITY (covers 'qty', 'quantity', 'onhand', 'stock', 'pcs', 'count', 'inventory', etc.)
                } elseif (in_array($colClean, ['qty', 'quantity', 'qnty', 'quant', 'onhand', 'stock', 'pcs', 'pieces', 'count', 'units', 'inventory', 'bal', 'balance'])
                    || str_contains($colClean, 'onhand')
                    || str_contains($colClean, 'quantity')
                    || str_contains($colClean, 'stockqty')
                    || str_contains($colClean, 'invqty')
                    || str_contains($colClean, 'inventoryqty')
                    || (str_contains($colClean, 'qty') && !str_contains($colClean, 'forecast') && !str_contains($colClean, 'avail') && !str_contains($colClean, 'reserv') && !str_contains($colClean, 'hist') && !str_contains($colClean, 'orig') && !str_contains($colClean, 'stat') && !str_contains($colClean, 'unfound') && !str_contains($colClean, 'acu'))
                    || str_contains($currentParent, 'inventory')
                ) {
                    $colKey = 'quantity';
                }

                // 19. Fallbacks based directly on subVal if colKey still unset
                if (!$colKey && !empty($subVal)) {
                    if ($subVal === 'onhand' || $subVal === 'qty' || str_contains($subVal, 'qty')) {
                        if (str_contains($subVal, 'acu') || str_contains($subVal, 'total')) {
                            $colKey = 'acu_quantity';
                        } else {
                            $colKey = 'quantity';
                        }
                    } elseif (str_contains($subVal, 'totalonhand') || str_contains($subVal, 'acu')) {
                        $colKey = 'acu_quantity';
                    } elseif (str_contains($subVal, 'totalsqm') || str_contains($subVal, 'totalavailablesqm')) {
                        $colKey = 'available_sqm';
                    } elseif (str_contains($subVal, 'perpanelsqm')) {
                        $colKey = 'per_panel_sqm';
                    } elseif (str_contains($subVal, 'sqm')) {
                        $colKey = 'sqm';
                    } elseif (str_contains($subVal, 'serial')) {
                        $colKey = 'tag_number';
                    } elseif (str_contains($subVal, 'particular')) {
                        $colKey = 'item_description';
                    } elseif (str_contains($subVal, 'status')) {
                        $colKey = 'status_particular';
                    }
                }

                $columnMap[$colIdx] = $colKey;
            }

            $startDataIdx = ($headerRow2Idx ?? $headerRow1Idx) + 1;
            $importedCount = 0;
            $updatedCount = 0;
            $lastItemModel = null;
            $activeScreenSize = null;

            for ($i = $startDataIdx; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                    continue; // Skip empty rows
                }

                // Check if summary row
                $rowJoined = strtolower(implode(' ', $row));
                if (str_contains($rowJoined, 'total count') || str_contains($rowJoined, 'total inventory')) {
                    continue;
                }

                // Check for Screen Size Divider row (e.g. ['10"', '', '', '', ''])
                $nonEmptyCells = array_values(array_filter(array_map('trim', $row), fn($v) => $v !== ''));
                if (count($nonEmptyCells) === 1 && preg_match('/^(\d+(?:\.\d+)?)\s*["\']?$/', $nonEmptyCells[0], $sm)) {
                    $activeScreenSize = $sm[1] . '"';
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
                $tagNumber = $cleanStr($rowData['tag_number'] ?? '');

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

                // If onHand is missing but totalOnHand is present:
                if ($onHand === null && $totalOnHand !== null) {
                    $onHand = $totalOnHand;
                }
                // If totalOnHand is missing but onHand is present:
                if ($totalOnHand === null && $onHand !== null) {
                    $totalOnHand = $onHand;
                }
                // If onHand is missing but tag_number has serials:
                if ($onHand === null && !empty($tagNumber)) {
                    $serials = preg_split('/[\r\n,;|]+/', $tagNumber, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($serials) > 0) {
                        $onHand = count($serials);
                        if ($totalOnHand === null) {
                            $totalOnHand = $onHand;
                        }
                    }
                }

                $isPhilipsCategory = str_contains(strtolower($category), 'philips');
                $defaultMfg = $isPhilipsCategory ? 'PHILIPS' : 'UNILUMIN';
                $defaultLoc = $isPhilipsCategory ? 'MARIKINA' : 'Globaltronics';

                if (!empty($model) || !empty($desc)) {
                    $itemData = [
                        'category' => !empty($rowData['category']) ? $cleanStr($rowData['category']) : $category,
                        'tag_number' => $tagNumber,
                        'po_number' => substr($po, 0, 100),
                        'manufacturer' => substr($mfg ?: $defaultMfg, 0, 100),
                        'model' => substr($model ?: substr($desc, 0, 50), 0, 100),
                        'item_description' => $desc ?: $model,
                        'location' => substr($location ?: $defaultLoc, 0, 100),
                        'quantity' => $onHand ?? 0,
                    ];

                    if ($hasColumn('acu_quantity')) {
                        $itemData['acu_quantity'] = $totalOnHand !== null ? $totalOnHand : ($onHand ?? 0);
                    }
                    if ($hasColumn('sqm')) {
                        $itemData['sqm'] = $sqm;
                    }
                    if ($hasColumn('forecasted_quantity')) {
                        $itemData['forecasted_quantity'] = $availQty;
                    }
                    if ($hasColumn('original_quantity')) {
                        $itemData['original_quantity'] = $origQty !== null ? $origQty : ($totalOnHand ?? ($onHand ?? 0));
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

                    // Auto assign active screen size from divider row if empty
                    if (empty($itemData['screen_size']) && $hasColumn('screen_size') && $activeScreenSize) {
                        $itemData['screen_size'] = $activeScreenSize;
                    }

                    // Auto extract screen size from description if still empty
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

                    // Match existing item accurately to avoid creating unnecessary duplicates or failing to update qty
                    $existing = null;
                    $targetCategory = $itemData['category'];
                    $categoryVariants = array_unique([$targetCategory, strtoupper($targetCategory), strtolower($targetCategory), 'Philips Service Units', 'PHILIPS SERVICE UNITS']);

                    if (!empty($itemData['tag_number'])) {
                        $tQuery = InventoryItem::whereIn('category', $categoryVariants)
                            ->where('tag_number', $itemData['tag_number']);
                        if (!empty($itemData['po_number'])) {
                            $tQuery->where('po_number', $itemData['po_number']);
                        }
                        $existing = $tQuery->first();
                    }

                    if (!$existing && !empty($itemData['model'])) {
                        // 1. Try matching category + model + location (if location was explicitly provided)
                        $mQuery = InventoryItem::whereIn('category', $categoryVariants)
                            ->where('model', $itemData['model']);

                        if (!empty($location)) {
                            $mQuery->where('location', $itemData['location']);
                        }

                        if (!empty($itemData['po_number'])) {
                            $mQuery->where('po_number', $itemData['po_number']);
                        }

                        $existing = $mQuery->first();

                        // 2. If no location match or location was omitted, match first existing item with this model in this category
                        if (!$existing) {
                            $existing = InventoryItem::whereIn('category', $categoryVariants)
                                ->where('model', $itemData['model'])
                                ->first();
                        }
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
                        // When updating: preserve existing values if CSV cell was blank/omitted
                        if ($onHand === null) {
                            unset($safeData['quantity']);
                        }
                        if ($totalOnHand === null && isset($safeData['acu_quantity'])) {
                            unset($safeData['acu_quantity']);
                        }
                        if (empty($tagNumber) && isset($safeData['tag_number'])) {
                            unset($safeData['tag_number']); // Don't wipe existing serials if not in CSV
                        }
                        if (empty($location) && isset($safeData['location'])) {
                            unset($safeData['location']); // Don't overwrite existing warehouse location with default
                        }
                        if (empty($po) && isset($safeData['po_number'])) {
                            unset($safeData['po_number']);
                        }

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
                    if (!empty($rowData['tag_number']) && $hasColumn('tag_number')) {
                        $existingTag = (string)($lastItemModel->tag_number ?? '');
                        $newTag = $cleanStr($rowData['tag_number']);
                        if (!empty($newTag) && !str_contains($existingTag, $newTag)) {
                            $lastItemModel->tag_number = $existingTag ? $existingTag . '; ' . $newTag : $newTag;
                            $needsSave = true;
                        }
                    }
                    if (($statusQty || !empty($statusPart)) && $hasColumn('status_qty')) {
                        $lastItemModel->status_qty = ($lastItemModel->status_qty ?? 0) + ($statusQty ?? 0);
                        if (!empty($statusPart)) {
                            $existingStat = (string)($lastItemModel->status_particular ?? '');
                            if ($existingStat === '' || $existingStat === 'OK') {
                                $lastItemModel->status_particular = $statusPart;
                            } elseif (!str_contains($existingStat, $statusPart)) {
                                $lastItemModel->status_particular = $existingStat . '; ' . $statusPart;
                            }
                        }
                        if (!isset($movements['unfound'])) $movements['unfound'] = [];
                        $movements['unfound'][] = [
                            'qty' => $statusQty ?? 0,
                            'status' => $statusPart ?: 'UNFOUND',
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

    /**
     * Resolve all possible category naming variants (case, plural/singular, formatting).
     *
     * @return array<int, string>
     */
    protected function getCategoryVariants(string $cat): array
    {
        $c = trim($cat);
        $variants = [$c, strtolower($c), strtoupper($c), ucwords(strtolower($c))];

        if (stripos($c, 'LED Service') !== false || stripos($c, 'LED SERVICES') !== false) {
            $variants = array_merge($variants, [
                'LED Service Units',
                'LED SERVICES UNITS',
                'LED SERVICE UNITS',
                'LED Service Unit',
                'LED SERVICES UNIT',
                'LED SERVICE UNIT',
            ]);
        } elseif (stripos($c, 'Philips Service') !== false) {
            $variants = array_merge($variants, [
                'Philips Service Units',
                'PHILIPS SERVICE UNITS',
                'Philips Service Unit',
                'PHILIPS SERVICE UNIT',
            ]);
        } elseif (stripos($c, 'Centralized LED') !== false) {
            $variants = array_merge($variants, [
                'CENTRALIZED LED INVENTORY',
                'Centralized LED Inventory',
                'CENTRALIZED LED',
                'Centralized LED',
            ]);
        } elseif (stripos($c, 'EOL Philips') !== false) {
            $variants = array_merge($variants, [
                'EOL PHILIPS UNITS',
                'EOL Philips Units',
                'EOL PHILIPS UNIT',
                'EOL Philips Unit',
                'EOL PHILIPS',
                'EOL Philips',
            ]);
        } elseif (stripos($c, 'Video Controller') !== false || stripos($c, 'Processor') !== false) {
            $variants = array_merge($variants, [
                'Video Controllers / Processors',
                'VIDEO CONTROLLERS / PROCESSORS',
                'Video Controllers',
                'Video Processors',
                'Processors',
            ]);
        } elseif (stripos($c, 'Digital iPoster') !== false || stripos($c, 'iPoster') !== false) {
            $variants = array_merge($variants, [
                'Digital iPoster',
                'DIGITAL IPOSTER',
                'Digital IPoster',
                'iPoster',
                'IPoster',
            ]);
        } elseif (stripos($c, 'Kiosk') !== false) {
            $variants = array_merge($variants, [
                'Kiosks',
                'KIOSKS',
                'Kiosk',
                'KIOSK',
            ]);
        } elseif (stripos($c, 'Shuttle') !== false) {
            $variants = array_merge($variants, [
                'Shuttle',
                'SHUTTLE',
            ]);
        } elseif (stripos($c, 'Aver') !== false) {
            $variants = array_merge($variants, [
                'Aver',
                'AVER',
            ]);
        }

        return array_values(array_unique($variants));
    }
}

