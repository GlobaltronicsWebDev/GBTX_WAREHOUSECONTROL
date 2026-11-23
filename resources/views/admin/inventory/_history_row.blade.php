<div data-history-row data-history-index="{{ $index }}" class="grid gap-3 border border-[#E5E7EB] p-3 sm:grid-cols-[120px_minmax(0,1fr)_auto]">
    <div>
        <label for="history-quantity-{{ $index }}" class="mb-1 block text-[11px] font-medium text-slate-700">Quantity</label>
        <input id="history-quantity-{{ $index }}" name="histories[{{ $index }}][quantity]" value="{{ $entry['quantity'] ?? '' }}" type="number" min="0" max="4294967295" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
    </div>
    <div>
        <label for="history-project-{{ $index }}" class="mb-1 block text-[11px] font-medium text-slate-700">Project / Client Description</label>
        <textarea id="history-project-{{ $index }}" name="histories[{{ $index }}][project]" rows="2" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">{{ $entry['project'] ?? '' }}</textarea>
    </div>
    <button type="button" data-remove-history hidden class="self-end px-2 py-2 text-[11px] text-red-600 hover:underline">Remove</button>
</div>
