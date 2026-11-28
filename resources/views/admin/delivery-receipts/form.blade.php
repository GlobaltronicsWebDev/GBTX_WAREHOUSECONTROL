@extends('layouts.operations-dashboard')

@section('title', $receipt->exists ? 'Edit Delivery Receipt' : 'New Delivery Receipt')
@section('breadcrumb', 'Logistics / Delivery Receipts / Draft')

@section('content')
    <div class="mx-auto max-w-6xl space-y-4 px-3 py-4 sm:px-5">
        <div>
            <h1 class="text-[20px] font-semibold text-slate-900">{{ $receipt->exists ? 'Edit '.$receipt->number : 'New Delivery Receipt' }}</h1>
            <p class="mt-1 text-[12px] text-slate-500">Saving a draft does not change inventory. Releasing it deducts stock and creates a Stock Out; do not record a separate Stock Out for the same delivery.</p>
        </div>
        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700">
                <p class="font-medium">Please correct the following:</p>
                <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if ($inventoryItems->isEmpty())
            <p role="status" class="border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-800">No inventory items are assigned to a warehouse. <a class="underline" href="{{ route('admin.inventory.create') }}">Add an inventory item</a> first.</p>
        @endif
        <form method="POST" action="{{ $receipt->exists ? route('admin.delivery-receipts.update', $receipt) : route('admin.delivery-receipts.store') }}" class="space-y-5 border border-[#E5E7EB] bg-white p-4 sm:p-5">
            @csrf
            @if ($receipt->exists) @method('PUT') @endif
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="type" class="mb-1 block text-[11px] font-medium text-slate-700">Receipt Type <span class="text-red-500">*</span></label>
                    @if ($receipt->exists)
                        <input type="hidden" name="type" value="{{ $receipt->type }}">
                        <p class="py-2 text-[12px]">{{ ucfirst($receipt->type) }} (cannot be changed)</p>
                    @else
                        <select id="type" name="type" required class="w-full rounded-sm border border-[#E5E7EB] bg-white px-3 py-2 text-[12px]">
                            <option value="client" @selected(old('type') === 'client')>DR to Client</option>
                            <option value="internal" @selected(old('type') === 'internal')>DR Internal</option>
                        </select>
                    @endif
                </div>
                <div>
                    <label for="receipt_date" class="mb-1 block text-[11px] font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
                    <input id="receipt_date" name="receipt_date" type="date" required value="{{ old('receipt_date', $receipt->receipt_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">
                </div>
                <div>
                    <label for="recipient" class="mb-1 block text-[11px] font-medium text-slate-700">Client / Site <span class="text-red-500">*</span></label>
                    <input id="recipient" name="recipient" required maxlength="255" value="{{ old('recipient', $receipt->recipient) }}" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">
                </div>
                @foreach (['address' => 'Address', 'tin' => 'Client TIN', 'contact_person' => 'Contact Person', 'terms' => 'Terms', 'purchase_order_number' => 'PO No.', 'sales_order_number' => 'SO No.', 'request_number' => 'SR / SRF No.'] as $name => $label)
                    <div>
                        <label for="{{ $name }}" class="mb-1 block text-[11px] font-medium text-slate-700">{{ $label }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" maxlength="255" value="{{ old($name, $receipt->{$name}) }}" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">
                    </div>
                @endforeach
                @if (! $receipt->exists || $receipt->type === 'internal')
                    <div id="received-by-field" @if (! $receipt->exists && old('type', 'client') !== 'internal') hidden @endif>
                        <label for="received_by" class="mb-1 block text-[11px] font-medium text-slate-700">Received By</label>
                        <input id="received_by" name="received_by" maxlength="255" value="{{ old('received_by', $receipt->received_by) }}" @disabled(! $receipt->exists && old('type', 'client') !== 'internal') class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">
                    </div>
                @endif
            </section>
            <section aria-labelledby="receipt-lines-heading" class="space-y-3">
                <div class="flex items-center justify-between gap-2 border-b border-[#E5E7EB] pb-2">
                    <div>
                        <h2 id="receipt-lines-heading" class="text-[13px] font-semibold">Products</h2>
                        <p class="mt-1 text-[10px] text-slate-500">Availability is checked again when the receipt is released.</p>
                    </div>
                    <button type="button" id="add-receipt-line" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[11px] text-[#2563EB] hover:bg-[#F8F9FA]">Add Product</button>
                </div>
                <div id="receipt-lines" class="space-y-2">
                    @foreach ($lines as $index => $line)
                        @include('admin.delivery-receipts._line', ['index' => $index, 'line' => is_array($line) ? $line : []])
                    @endforeach
                </div>
                <template id="receipt-line-template">@include('admin.delivery-receipts._line', ['index' => '__INDEX__', 'line' => []])</template>
            </section>
            <div>
                <label for="remarks" class="mb-1 block text-[11px] font-medium text-slate-700">Special Instructions / Remarks</label>
                <textarea id="remarks" name="remarks" rows="3" maxlength="10000" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">{{ old('remarks', $receipt->remarks) }}</textarea>
            </div>
            <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-4">
                <a href="{{ $receipt->exists ? route('admin.delivery-receipts.show', $receipt) : route('admin.delivery-receipts.index') }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-600">Cancel</a>
                <button type="submit" @disabled($inventoryItems->isEmpty()) class="rounded-sm bg-[#2563EB] px-3 py-2 text-[12px] font-medium text-white disabled:cursor-not-allowed disabled:opacity-50">Save Draft</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const receiptType = document.getElementById('type');
        const receivedByField = document.getElementById('received-by-field');
        if (receiptType && receivedByField) {
            const updateReceivedByField = () => {
                const isInternal = receiptType.value === 'internal';
                receivedByField.hidden = !isInternal;
                document.getElementById('received_by').disabled = !isInternal;
            };
            receiptType.addEventListener('change', updateReceivedByField);
            updateReceivedByField();
        }

        const receiptLines = document.getElementById('receipt-lines');
        const receiptTemplate = document.getElementById('receipt-line-template');
        let nextReceiptIndex = Array.from(receiptLines.querySelectorAll('[data-receipt-line]'))
            .reduce((maximum, line) => Math.max(maximum, Number(line.dataset.lineIndex) || 0), -1) + 1;

        const updateReceiptAvailability = (line) => {
            const option = line.querySelector('[data-receipt-item]').selectedOptions[0];
            line.querySelector('[data-receipt-availability]').textContent = option.dataset.available === undefined
                ? 'Select an item to see its current available quantity.'
                : `Available quantity: ${option.dataset.available} ${option.dataset.unit}`;
        };
        receiptLines.querySelectorAll('[data-receipt-line]').forEach(updateReceiptAvailability);
        document.getElementById('add-receipt-line').addEventListener('click', () => {
            receiptLines.insertAdjacentHTML('beforeend', receiptTemplate.innerHTML.replaceAll('__INDEX__', String(nextReceiptIndex++)));
            updateReceiptAvailability(receiptLines.lastElementChild);
        });
        receiptLines.addEventListener('change', (event) => {
            if (event.target.matches('[data-receipt-item]')) {
                updateReceiptAvailability(event.target.closest('[data-receipt-line]'));
            }
        });
        receiptLines.addEventListener('click', (event) => {
            if (event.target.matches('[data-remove-receipt-line]')) {
                event.target.closest('[data-receipt-line]').remove();
            }
        });
    </script>
@endpush
