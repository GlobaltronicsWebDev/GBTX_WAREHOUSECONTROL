<div data-receipt-line data-line-index="{{ $index }}" class="grid gap-3 border border-[#E5E7EB] p-3 sm:grid-cols-[minmax(0,2fr)_120px_minmax(0,1fr)_auto] sm:items-end">
    <div class="sm:contents">
        <div>
            <div data-receipt-search-container hidden class="mb-2">
                <label for="receipt-search-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Search product</label>
                <input id="receipt-search-{{ $index }}" type="search" data-receipt-search autocomplete="off" placeholder="Search by SKU or product name" class="w-full rounded-sm border border-[#E5E7EB] px-2 py-2 text-[11px] outline-none focus:border-[#2563EB]">
                <p data-receipt-search-status aria-live="polite" class="mt-1 text-[10px] text-slate-500"></p>
            </div>
            <label for="receipt-item-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Product ID / SKU and Product Name <span class="text-red-500">*</span></label>
            <select id="receipt-item-{{ $index }}" name="lines[{{ $index }}][inventory_item_id]" data-receipt-item required class="w-full rounded-sm border border-[#E5E7EB] bg-white px-2 py-2 text-[11px] outline-none focus:border-[#2563EB]">
                <option value="">Select product</option>
                @foreach ($inventoryItems as $item)
                    @php
                        $available = min((int) $item->available_qty, (int) $item->on_hand, (int) $item->total_on_hand);
                    @endphp
                    <option value="{{ $item->id }}" data-unit="{{ $item->unit_of_measurement }}" data-available="{{ $available }}" @selected((string) ($line['inventory_item_id'] ?? '') === (string) $item->id)>{{ $item->po_sku_number ?: 'ID-'.$item->id }} — {{ $item->item_description }} ({{ $item->warehouseLocation?->name }}{{ $item->sub_location ? ' / '.$item->sub_location : '' }})</option>
                @endforeach
            </select>
        </div>
        <p data-receipt-availability aria-live="polite" class="mt-1 text-[10px] text-slate-500 sm:col-start-1 sm:row-start-2 sm:-mt-2"></p>
    </div>
    <div>
        <label for="receipt-quantity-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Quantity <span class="text-red-500">*</span></label>
        <input id="receipt-quantity-{{ $index }}" name="lines[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? '' }}" type="number" min="1" max="4294967295" step="1" required class="w-full rounded-sm border border-[#E5E7EB] px-2 py-2 text-[11px] outline-none focus:border-[#2563EB]">
    </div>
    <div>
        <label for="receipt-model-{{ $index }}" class="mb-1 block text-[10px] font-medium text-slate-700">Model / Serial No.</label>
        <input id="receipt-model-{{ $index }}" name="lines[{{ $index }}][model_serial]" value="{{ $line['model_serial'] ?? '' }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-2 py-2 text-[11px] outline-none focus:border-[#2563EB]">
    </div>
    <button type="button" data-remove-receipt-line class="rounded-sm px-2 py-2 text-[11px] text-red-600 hover:bg-red-50 hover:underline">Remove</button>
</div>
