@extends('layouts.operations-dashboard')

@php
    $transactionLabel = $type === 'in' ? 'Stock In' : 'Stock Out';
    $partyLabel = $type === 'in' ? 'Supplier' : 'Department / Customer';
    $partyValue = $type === 'in' ? $transaction->supplier_name : $transaction->department_customer;
@endphp

@section('title', $transactionLabel.' '.$transaction->reference_number)
@section('breadcrumb', 'Inventory / '.$transactionLabel.' / '.$transaction->reference_number)

@section('content')
    <style>
        @media print {
            #dashboardSidebar, #dashboardSidebarBackdrop, header, .print-hidden {
                display: none !important;
            }

            body, main {
                background: #fff !important;
                color: #000 !important;
            }
        }
    </style>

    <div class="mx-auto max-w-5xl space-y-4 px-3 py-4 sm:px-5">
        @if (session('status'))
            <div role="status" class="print-hidden border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        <div class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-[20px] font-semibold tracking-tight text-slate-900">{{ $transactionLabel }} {{ $transaction->reference_number }}</h1>
                <p class="mt-1 text-[12px] text-slate-500">Transaction date: {{ $transaction->transaction_date?->format('Y-m-d') }}</p>
            </div>
            <div class="print-hidden flex gap-2">
                <button type="button" onclick="window.print()" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">Print Transaction</button>
                <a href="{{ route('admin.stock-'.$type.'.index') }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">Back to Report</a>
            </div>
        </div>

        <section class="grid gap-3 border border-[#E5E7EB] bg-white p-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><p class="text-[10px] uppercase text-slate-500">Reference Number</p><p class="mt-1 font-mono text-[12px] font-medium">{{ $transaction->reference_number }}</p></div>
            <div><p class="text-[10px] uppercase text-slate-500">{{ $partyLabel }}</p><p class="mt-1 text-[12px]">{{ $partyValue ?: '—' }}</p></div>
            @if ($type === 'in')
                <div><p class="text-[10px] uppercase text-slate-500">Purchase Order Number</p><p class="mt-1 text-[12px]">{{ $transaction->purchase_order_number ?: '—' }}</p></div>
                <div><p class="text-[10px] uppercase text-slate-500">Received By</p><p class="mt-1 text-[12px]">{{ $transaction->received_by }}</p></div>
            @else
                <div><p class="text-[10px] uppercase text-slate-500">Request Number</p><p class="mt-1 text-[12px]">{{ $transaction->request_number ?: '—' }}</p></div>
                <div><p class="text-[10px] uppercase text-slate-500">Released By</p><p class="mt-1 text-[12px]">{{ $transaction->released_by }}</p></div>
                <div><p class="text-[10px] uppercase text-slate-500">Received By</p><p class="mt-1 text-[12px]">{{ $transaction->received_by }}</p></div>
            @endif
            <div><p class="text-[10px] uppercase text-slate-500">Recorded By</p><p class="mt-1 text-[12px]">{{ $transaction->creator?->name ?: '—' }}</p></div>
            <div class="sm:col-span-2 lg:col-span-3"><p class="text-[10px] uppercase text-slate-500">{{ $type === 'in' ? 'Remarks' : 'Purpose / Remarks' }}</p><p class="mt-1 whitespace-pre-line text-[12px]">{{ $transaction->remarks ?: '—' }}</p></div>
        </section>

        <section class="border border-[#E5E7EB] bg-white">
            <h2 class="border-b border-[#E5E7EB] px-4 py-3 text-[12px] font-semibold">Products</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-170 text-left text-[11px]">
                    <thead class="bg-[#F8F9FA] text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Product ID / SKU</th>
                            <th class="px-4 py-2">Product Name</th>
                            <th class="px-4 py-2">Warehouse Location</th>
                            <th class="px-4 py-2 text-right">Quantity</th>
                            <th class="px-4 py-2 text-right">Available Before</th>
                            <th class="px-4 py-2 text-right">Available After</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @foreach ($transaction->lines as $line)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $line->product_sku }}</td>
                                <td class="px-4 py-3">{{ $line->product_name }}</td>
                                <td class="px-4 py-3">{{ $line->warehouse_location_name ?: '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($line->quantity) }} {{ $line->unit_of_measurement }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($line->stock_before) }} {{ $line->unit_of_measurement }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($line->stock_after) }} {{ $line->unit_of_measurement }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        @if ($transaction->attachment_path)
            <p class="print-hidden text-[11px]"><a href="{{ route('admin.stock-'.$type.'.attachment', $transaction) }}" class="text-[#2563EB] underline">Download transaction attachment</a></p>
        @endif
    </div>
@endsection
