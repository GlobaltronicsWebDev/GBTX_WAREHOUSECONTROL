<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $items = InventoryItem::query()
            ->with(['warehouseLocation', 'histories'])
            ->matching($request->string('search')->toString())
            ->latest()
            ->paginate(25)
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

        return view('admin.inventory.create', compact('warehouseLocations', 'item'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedItem($request);
        $validated['created_by'] = $request->user()->id;
        DB::transaction(fn () => $this->saveItem(new InventoryItem, $validated));

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', 'Inventory item added successfully.');
    }

    public function update(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $validated = $this->validatedItem($request, $inventory);
        DB::transaction(fn () => $this->saveItem($inventory, $validated));

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

    /**
     * @param  array<string, mixed>  $validated
     */
    private function saveItem(InventoryItem $item, array $validated): void
    {
        $historiesSubmitted = ($validated['history_submitted'] ?? false) || array_key_exists('histories', $validated);
        $histories = array_values(array_filter($validated['histories'] ?? [], fn (array $history): bool => ($history['quantity'] ?? null) !== null || ($history['project'] ?? null) !== null));
        unset($validated['histories'], $validated['history_submitted']);

        if ($historiesSubmitted) {
            $validated['history_qty'] = $histories[0]['quantity'] ?? 0;
            $validated['history_project'] = $histories[0]['project'] ?? null;
        }

        $item->fill($validated)->saveOrFail();

        if ($historiesSubmitted) {
            $item->histories()->delete();
            $item->histories()->createMany($histories);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedItem(Request $request, ?InventoryItem $item = null): array
    {
        $warehouseRule = Rule::exists('warehouse_locations', 'id');
        $requestedWarehouseId = $request->input('warehouse_location_id');
        if ($item?->warehouse_location_id === null || ! is_scalar($requestedWarehouseId) || (string) $requestedWarehouseId !== (string) $item->warehouse_location_id) {
            $warehouseRule->where('is_active', true)->where('navigation_enabled', true);
        }

        $validated = $request->validate([
            'tag_number' => ['nullable', 'string', 'max:255'],
            'date_received' => ['nullable', 'date'],
            'po_sku_number' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model_pixel_pitch' => ['nullable', 'string', 'max:255'],
            'item_description' => ['required', 'string', 'max:10000'],
            'warehouse_location_id' => [
                'required',
                'integer',
                $warehouseRule,
            ],
            'on_hand' => ['required', 'integer', 'min:0'],
            'total_on_hand' => ['required', 'integer', 'min:0'],
            'per_panel_sqm' => ['required', 'numeric', 'min:0'],
            'total_available_sqm' => ['required', 'numeric', 'min:0'],
            'available_qty' => ['required', 'integer', 'min:0'],
            'available_sqm' => ['required', 'numeric', 'min:0'],
            'reservation_qty' => ['required', 'integer', 'min:0'],
            'reservation_project_details' => ['nullable', 'string', 'max:10000'],
            'history_submitted' => ['sometimes', 'boolean'],
            'histories' => ['sometimes', 'array'],
            'histories.*' => ['array:quantity,project'],
            'histories.*.quantity' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'histories.*.project' => ['nullable', 'string', 'max:10000'],
            'original_qty' => ['required', 'integer', 'min:0'],
            'status_qty' => ['required', 'integer', 'min:0'],
            'status_particular' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:10000'],
        ]);

        $warehouseLocation = WarehouseLocation::query()->findOrFail($validated['warehouse_location_id']);
        $subLocation = $request->validate([
            'sub_location' => ['nullable', 'string', Rule::in($warehouseLocation->inventorySubLocations())],
        ]);
        $validated['sub_location'] = $subLocation['sub_location'] ?? null;
        $validated['location'] = $warehouseLocation->name;

        return $validated;
    }
}
