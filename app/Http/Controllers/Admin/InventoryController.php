<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            'acu_quantity' => ['nullable', 'integer', 'min:0'],
            'forecasted_quantity' => ['nullable', 'integer', 'min:0'],
            'sqm' => ['nullable', 'numeric', 'min:0'],
            'location' => ['required', 'string', Rule::in($allowedLocations)],
            'status' => ['nullable', 'string', Rule::in(['in_stock', 'low_stock', 'out_of_stock', 'eol'])],
        ]);

        if (empty($validated['category'])) {
            $validated['category'] = 'CENTRALIZED LED INVENTORY';
        }

        if (empty($validated['status'])) {
            $validated['status'] = $validated['quantity'] <= 3 ? 'low_stock' : 'in_stock';
        }

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
            'acu_quantity' => ['nullable', 'integer', 'min:0'],
            'forecasted_quantity' => ['nullable', 'integer', 'min:0'],
            'sqm' => ['nullable', 'numeric', 'min:0'],
            'location' => ['required', 'string', Rule::in($allowedLocations)],
            'status' => ['nullable', 'string', Rule::in(['in_stock', 'low_stock', 'out_of_stock', 'eol'])],
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

        $item->update($validated);

        return redirect()->route('admin.inventory.index', ['category' => $item->category])
            ->with('status', "Inventory unit '{$item->model}' updated successfully.");
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
}
