<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\WarehouseLocation;
use App\Support\InventoryItemValidator;
use App\Support\InventoryItemWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class InventoryItemController extends Controller
{
    private const PAGE_SIZE = 25;

    public function dashboard(): View
    {
        $counts = InventoryItem::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN available_qty >= 6 THEN 1 END) as in_stock')
            ->selectRaw('COUNT(CASE WHEN available_qty BETWEEN 2 AND 5 THEN 1 END) as low_stock')
            ->selectRaw('COUNT(CASE WHEN available_qty = 1 THEN 1 END) as last_stock')
            ->selectRaw('COUNT(CASE WHEN available_qty = 0 THEN 1 END) as out_of_stock')
            ->firstOrFail();

        $totalItems = (int) $counts->total;
        $stockHealth = [
            'In Stock' => (int) $counts->in_stock,
            'Low Stock' => (int) $counts->low_stock,
            'Last Stock' => (int) $counts->last_stock,
            'Out of Stock' => (int) $counts->out_of_stock,
        ];

        $warehouseLocations = WarehouseLocation::query()
            ->where('is_active', true)
            ->where('navigation_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.inventory.dashboard', compact('totalItems', 'stockHealth', 'warehouseLocations'));
    }

    public function index(Request $request): View
    {
        $items = $this->inventoryListQuery($request)
            ->with(['warehouseLocation', 'histories'])
            ->paginate(self::PAGE_SIZE)
            ->withQueryString();

        return view('admin.inventory.index', compact('items'));
    }

    public function create(): View
    {
        return $this->itemForm();
    }

    public function edit(InventoryItem $inventory): View
    {
        return $this->itemForm($inventory->load('histories'));
    }

    private function itemForm(?InventoryItem $item = null): View
    {
        $warehouseLocations = WarehouseLocation::query()
            ->where(function ($query) use ($item): void {
                $query->where(function ($query): void {
                    $query->where('is_active', true)->where('navigation_enabled', true);
                });

                if ($item?->warehouse_location_id !== null) {
                    $query->orWhere('id', $item->warehouse_location_id);
                }
            })
            ->orderBy('name')
            ->get();
        $unitOfMeasurementLocked = $item?->stockTransactionLines()->exists() ?? false;

        return view('admin.inventory.create', compact('warehouseLocations', 'item', 'unitOfMeasurementLocked'));
    }

    public function store(Request $request, InventoryItemValidator $validator, InventoryItemWriter $writer): RedirectResponse
    {
        $validated = $validator->validate($request->all());
        $validated['created_by'] = $request->user()->id;
        DB::transaction(fn () => $writer->save(new InventoryItem, $validated));

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', 'Inventory item added successfully.');
    }

    public function update(Request $request, InventoryItem $inventory, InventoryItemValidator $validator, InventoryItemWriter $writer): RedirectResponse
    {
        DB::transaction(function () use ($request, $inventory, $validator, $writer): void {
            $lockedInventory = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($inventory->id);
            $validated = $validator->validate($request->all(), $lockedInventory);
            $writer->save($lockedInventory, $validated);
        });

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', 'Inventory item updated successfully.');
    }

    public function destroy(Request $request, InventoryItem $inventory): RedirectResponse
    {
        abort_unless($request->user()->canDeleteInventoryItems(), 403);

        abort_unless($inventory->delete(), 500, 'The inventory item could not be deleted.');

        return back()->with('status', 'Inventory item deleted successfully.');
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canDeleteInventoryItems(), 403);

        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1', 'max:'.self::PAGE_SIZE],
            'items.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        $count = DB::transaction(function () use ($request, $validated): int {
            $items = $this->inventoryListQuery($request)
                ->forPage((int) ($validated['page'] ?? 1), self::PAGE_SIZE)
                ->lockForUpdate()
                ->get()
                ->whereIn('id', $validated['items']);

            if ($items->count() !== count($validated['items'])) {
                throw ValidationException::withMessages([
                    'items' => 'Some selected items are unavailable or no longer on this page. Nothing was deleted.',
                ]);
            }

            foreach ($items as $item) {
                if (! $item->delete()) {
                    throw new RuntimeException('An inventory item could not be deleted.');
                }
            }

            return $items->count();
        });

        return back()->with('status', "Deleted {$count} selected inventory item(s).");
    }

    /** @return Builder<InventoryItem> */
    private function inventoryListQuery(Request $request): Builder
    {
        return InventoryItem::query()
            ->matching($request->string('search')->toString())
            ->latest()
            ->orderByDesc('id');
    }
}
