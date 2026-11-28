<div data-stock-line data-line-index="{{ $index }}" class="grid gap-3 border border-[#E5E7EB] p-3 sm:grid-cols-[minmax(0,2fr)_120px_140px_auto] sm:items-end">
    <div class="sm:contents">
        <div>
            <label for="stock-item-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Product ID / SKU and Product Name <span class="text-red-500">*</span></label>
            <select id="stock-item-{{ $index }}" name="lines[{{ $index }}][inventory_item_id]" data-inventory-item required class="w-full rounded-sm border border-[#E5E7EB] bg-white px-2 py-2 text-[11px] outline-none focus:border-[#2563EB]">
                <option value="">Select product</option>
                @foreach ($inventoryItems as $inventoryItem)
                    @php
                        $availableQuantity = min((int) $inventoryItem->available_qty, (int) $inventoryItem->on_hand, (int) $inventoryItem->total_on_hand);
                    @endphp
                    <option
                        value="{{ $inventoryItem->id }}"
                        data-available="{{ $availableQuantity }}"
                        data-unit="{{ $inventoryItem->unit_of_measurement }}"
                        @selected((string) ($line['inventory_item_id'] ?? '') === (string) $inventoryItem->id)
                    >{{ $inventoryItem->po_sku_number ?: 'ID-'.$inventoryItem->id }} — {{ $inventoryItem->item_description }} ({{ $inventoryItem->warehouseLocation?->name }}{{ $inventoryItem->sub_location ? ' / '.$inventoryItem->sub_location : '' }} · {{ $availableQuantity }} available)</option>
                @endforeach
            </select>
        </div>
        <p data-available-quantity aria-live="polite" class="mt-1 text-[10px] text-slate-500 sm:col-start-1 sm:row-start-2 sm:-mt-2"></p>
    </div>
    <div>
        <label for="stock-quantity-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Quantity <span class="text-red-500">*</span></label>
        <input id="stock-quantity-{{ $index }}" name="lines[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? '' }}" type="number" min="1" max="4294967295" step="1" required class="w-full rounded-sm border border-[#E5E7EB] px-2 py-2 text-[11px] outline-none focus:border-[#2563EB]">
    </div>
    <div>
        <label for="stock-uom-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Unit of Measurement <span class="text-red-500">*</span></label>
        <input id="stock-uom-{{ $index }}" name="lines[{{ $index }}][unit_of_measurement]" value="{{ $line['unit_of_measurement'] ?? '' }}" maxlength="50" required readonly class="w-full rounded-sm border border-[#E5E7EB] bg-slate-50 px-2 py-2 text-[11px] outline-none">
    </div>
    <button type="button" data-remove-stock-line class="rounded-sm px-2 py-2 text-[11px] text-red-600 hover:bg-red-50 hover:underline">Remove</button>
</div>
