@extends('layouts.operations-dashboard')

@php
    $transactionLabel = $type === 'in' ? 'Stock In' : 'Stock Out';
    $lines = is_array($lines) && count($lines) > 0 ? $lines : [['inventory_item_id' => '', 'quantity' => '', 'unit_of_measurement' => 'PCS']];
@endphp

@section('title', 'New '.$transactionLabel)
@section('breadcrumb', 'Inventory / '.$transactionLabel.' / New')

@section('content')
    <div class="mx-auto max-w-6xl space-y-4 px-3 py-4 sm:px-5">
        <div>
            <h1 class="text-[20px] font-semibold tracking-tight text-slate-900">New {{ $transactionLabel }} Transaction</h1>
            <p class="mt-1 text-[12px] text-slate-500">Reference number is generated automatically after the transaction is saved.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700">
                <p class="font-medium">Please correct the following:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($inventoryItems->isEmpty())
            <div role="status" class="border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-800">
                No inventory items are assigned to a warehouse yet.
                <a href="{{ route('admin.inventory.create') }}" class="font-medium underline">Add an inventory item</a> before recording stock movements.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.stock-'.$type.'.store') }}" enctype="multipart/form-data" class="space-y-5 border border-[#E5E7EB] bg-white p-4 sm:p-5">
            @csrf

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="transaction_date" class="mb-1 block text-[11px] font-medium text-slate-700">Transaction Date <span class="text-red-500">*</span></label>
                    <input id="transaction_date" name="transaction_date" type="date" required value="{{ old('transaction_date', now()->format('Y-m-d')) }}" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                </div>

                @if ($type === 'in')
                    <div>
                        <label for="purchase_order_number" class="mb-1 block text-[11px] font-medium text-slate-700">Purchase Order Number</label>
                        <input id="purchase_order_number" name="purchase_order_number" value="{{ old('purchase_order_number') }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                    <div>
                        <label for="supplier_name" class="mb-1 block text-[11px] font-medium text-slate-700">Supplier Name <span class="text-red-500">*</span></label>
                        <input id="supplier_name" name="supplier_name" required value="{{ old('supplier_name') }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                    <div>
                        <label for="received_by" class="mb-1 block text-[11px] font-medium text-slate-700">Received By <span class="text-red-500">*</span></label>
                        <input id="received_by" name="received_by" required value="{{ old('received_by', $authName) }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                @else
                    <div>
                        <label for="request_number" class="mb-1 block text-[11px] font-medium text-slate-700">Request Number</label>
                        <input id="request_number" name="request_number" value="{{ old('request_number') }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                    <div>
                        <label for="department_customer" class="mb-1 block text-[11px] font-medium text-slate-700">Department / Customer <span class="text-red-500">*</span></label>
                        <input id="department_customer" name="department_customer" required value="{{ old('department_customer') }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                    <div>
                        <label for="released_by" class="mb-1 block text-[11px] font-medium text-slate-700">Released By <span class="text-red-500">*</span></label>
                        <input id="released_by" name="released_by" required value="{{ old('released_by', $authName) }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                    <div>
                        <label for="received_by" class="mb-1 block text-[11px] font-medium text-slate-700">Received By <span class="text-red-500">*</span></label>
                        <input id="received_by" name="received_by" required value="{{ old('received_by') }}" maxlength="255" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                @endif
            </section>

            <section aria-labelledby="stock-lines-heading" class="space-y-3">
                <div class="flex items-center justify-between gap-2 border-b border-[#E5E7EB] pb-2">
                    <div>
                        <h2 id="stock-lines-heading" class="text-[13px] font-semibold text-slate-800">Products</h2>
                        <p class="mt-1 text-[10px] text-slate-500">Stock-out quantities are checked again against the locked inventory balance when saved.</p>
                    </div>
                    <button id="add-stock-line" type="button" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[11px] font-medium text-[#2563EB] hover:bg-[#F8F9FA]">Add Product</button>
                </div>
                <div id="stock-lines" class="space-y-2">
                    @foreach ($lines as $index => $line)
                        @include('admin.stock-transactions._line', ['index' => $index, 'line' => is_array($line) ? $line : [], 'type' => $type, 'inventoryItems' => $inventoryItems])
                    @endforeach
                </div>
                <template id="stock-line-template">
                    @include('admin.stock-transactions._line', ['index' => '__INDEX__', 'line' => [], 'type' => $type, 'inventoryItems' => $inventoryItems])
                </template>
            </section>

            <section class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="remarks" class="mb-1 block text-[11px] font-medium text-slate-700">{{ $type === 'in' ? 'Remarks' : 'Purpose / Remarks' }}</label>
                    <textarea id="remarks" name="remarks" rows="3" maxlength="10000" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">{{ old('remarks') }}</textarea>
                </div>
                <div>
                    <label for="attachment" class="mb-1 block text-[11px] font-medium text-slate-700">Attachment</label>
                    <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">
                    <p class="mt-1 text-[10px] text-slate-500">Delivery receipt, invoice, or request form. PDF/JPG/PNG up to 10 MB.</p>
                </div>
            </section>

            <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-4">
                <a href="{{ route('admin.stock-'.$type.'.index') }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-600 hover:bg-[#F8F9FA]">Cancel</a>
                <button type="submit" @disabled($inventoryItems->isEmpty()) class="rounded-sm bg-[#2563EB] px-3 py-2 text-[12px] font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Save {{ $transactionLabel }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const stockLines = document.getElementById('stock-lines');
        const stockLineTemplate = document.getElementById('stock-line-template');
        let nextStockLineIndex = Array.from(stockLines.querySelectorAll('[data-stock-line]'))
            .reduce((maximum, line) => Math.max(maximum, Number(line.dataset.lineIndex) || 0), -1) + 1;

        const updateStockLine = (line) => {
            const product = line.querySelector('[data-inventory-item]');
            const available = product.selectedOptions[0]?.dataset.available;
            const unit = product.selectedOptions[0]?.dataset.unit;
            const availableLabel = line.querySelector('[data-available-quantity]');
            line.querySelector('[name$="[unit_of_measurement]"]').value = unit || '';
            availableLabel.textContent = available === undefined ? 'Select an item to see its current available quantity.' : `Available quantity: ${available}`;
        };

        stockLines.querySelectorAll('[data-stock-line]').forEach(updateStockLine);

        document.getElementById('add-stock-line').addEventListener('click', () => {
            stockLines.insertAdjacentHTML('beforeend', stockLineTemplate.innerHTML.replaceAll('__INDEX__', String(nextStockLineIndex++)));
            updateStockLine(stockLines.lastElementChild);
        });

        stockLines.addEventListener('change', (event) => {
            if (event.target.matches('[data-inventory-item]')) {
                updateStockLine(event.target.closest('[data-stock-line]'));
            }
        });

        stockLines.addEventListener('click', (event) => {
            if (event.target.matches('[data-remove-stock-line]')) {
                event.target.closest('[data-stock-line]').remove();
            }
        });
    </script>
@endpush
