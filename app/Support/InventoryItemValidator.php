<?php

namespace App\Support;

use App\Models\InventoryItem;
use App\Models\WarehouseLocation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventoryItemValidator
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(array $data, ?InventoryItem $item = null, bool $allowExistingWithoutWarehouse = false): array
    {
        $warehouseRule = Rule::exists('warehouse_locations', 'id');
        $requestedWarehouseId = $data['warehouse_location_id'] ?? null;
        if ($item?->warehouse_location_id === null || ! is_scalar($requestedWarehouseId) || (string) $requestedWarehouseId !== (string) $item->warehouse_location_id) {
            $warehouseRule->where('is_active', true)->where('navigation_enabled', true);
        }

        $allowMissingWarehouse = $allowExistingWithoutWarehouse && $item !== null && $item->warehouse_location_id === null && empty($requestedWarehouseId);

        $validated = Validator::make($data, [
            'tag_number' => ['nullable', 'string', 'max:255'],
            'date_received' => ['nullable', 'date'],
            'po_sku_number' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model_pixel_pitch' => ['nullable', 'string', 'max:255'],
            'item_description' => ['required', 'string', 'max:10000'],
            'warehouse_location_id' => [$allowMissingWarehouse ? 'nullable' : 'required', 'integer', $warehouseRule],
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
        ])->validate();

        $warehouseLocation = isset($validated['warehouse_location_id'])
            ? WarehouseLocation::query()->findOrFail($validated['warehouse_location_id'])
            : null;
        $subLocation = Validator::make($data, [
            'sub_location' => ['nullable', 'string', Rule::in($warehouseLocation?->inventorySubLocations() ?? [])],
        ])->validate();
        $validated['sub_location'] = $subLocation['sub_location'] ?? null;
        $validated['location'] = $warehouseLocation?->name ?? $item?->location;

        return $validated;
    }
}
