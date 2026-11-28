@extends('layouts.operations-dashboard')

@php
    $transactionLabel = $type === 'in' ? 'Stock In' : 'Stock Out';
    $partyLabel = $type === 'in' ? 'Supplier' : 'Department / Customer';
@endphp

@section('title', $transactionLabel.' Report')
@section('breadcrumb', 'Inventory / '.$transactionLabel)

@section('content')
    <style>
        @media print {
            #dashboardSidebar, #dashboardSidebarBackdrop, header, .print-hidden, nav[aria-label="Pagination"] {
                display: none !important;
            }

            body, main {
                background: #fff !important;
                color: #000 !important;
            }
        }
    </style>

    <div class="space-y-4 px-3 py-4 sm:px-5">
        <div class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-[20px] font-semibold tracking-tight text-slate-900">{{ $transactionLabel }} Management</h1>
                <p class="mt-1 text-[12px] text-slate-500">Record and review warehouse {{ strtolower($transactionLabel) }} transactions.</p>
            </div>
            <div class="print-hidden flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">Print Report</button>
                <a href="{{ route('admin.stock-'.$type.'.export', request()->query()) }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">Export CSV</a>
                <a href="{{ route('admin.stock-'.$type.'.create') }}" class="rounded-sm bg-[#2563EB] px-3 py-2 text-[12px] font-medium text-white hover:bg-blue-700">New {{ $transactionLabel }}</a>
            </div>
        </div>

        @if (session('status'))
            <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="border border-[#E5E7EB] bg-white">
            <form method="GET" action="{{ route('admin.stock-'.$type.'.index') }}" class="print-hidden flex flex-col gap-2 border-b border-[#E5E7EB] p-3 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <label for="stock-search" class="mb-1 block text-[10px] font-medium text-slate-600">Search</label>
                    <input id="stock-search" name="search" value="{{ $filters['search'] }}" placeholder="Reference, supplier, department, SKU or product" class="h-9 w-full border border-[#E5E7EB] px-2 text-[11px] outline-none focus:border-[#2563EB]">
                </div>
                <div>
                    <label for="stock-from" class="mb-1 block text-[10px] font-medium text-slate-600">From</label>
                    <input id="stock-from" type="date" name="from" value="{{ $filters['from'] }}" class="h-9 border border-[#E5E7EB] px-2 text-[11px] outline-none focus:border-[#2563EB]">
                </div>
                <div>
                    <label for="stock-to" class="mb-1 block text-[10px] font-medium text-slate-600">To</label>
                    <input id="stock-to" type="date" name="to" value="{{ $filters['to'] }}" class="h-9 border border-[#E5E7EB] px-2 text-[11px] outline-none focus:border-[#2563EB]">
                </div>
                <button type="submit" class="h-9 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Filter</button>
                @if ($filters['search'] || $filters['from'] || $filters['to'])
                    <a href="{{ route('admin.stock-'.$type.'.index') }}" class="px-2 py-2 text-[10px] text-slate-500 underline">Clear</a>
                @endif
                <span class="text-[10px] text-slate-500 lg:ml-auto">{{ number_format($transactions->total()) }} transactions</span>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-245 border-collapse text-left text-[11px]">
                    <thead class="h-9 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3">Reference / Date</th>
                            <th class="px-3">{{ $partyLabel }}</th>
                            <th class="px-3">Products</th>
                            <th class="px-3">Warehouse</th>
                            <th class="px-3">Handled By</th>
                            <th class="px-3 print-hidden">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($transactions as $transaction)
                            <tr class="align-top text-slate-700">
                                <td class="whitespace-nowrap px-3 py-3">
                                    <a href="{{ route('admin.stock-'.$type.'.show', $transaction) }}" class="font-mono font-medium text-[#2563EB] hover:underline">{{ $transaction->reference_number }}</a>
                                    <span class="mt-1 block text-[10px] text-slate-500">{{ $transaction->transaction_date?->format('Y-m-d') }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    {{ $type === 'in' ? ($transaction->supplier_name ?: '—') : ($transaction->department_customer ?: '—') }}
                                    @if ($type === 'in' && $transaction->purchase_order_number)
                                        <span class="mt-1 block text-[10px] text-slate-500">PO {{ $transaction->purchase_order_number }}</span>
                                    @elseif ($type === 'out' && $transaction->request_number)
                                        <span class="mt-1 block text-[10px] text-slate-500">Request {{ $transaction->request_number }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @foreach ($transaction->lines as $line)
                                        <div @class(['py-0.5', '!border-t !border-[#E5E7EB] mt-1 pt-1' => ! $loop->first])>
                                            <span class="font-medium">{{ $line->product_sku }}</span>
                                            <span> · {{ $line->product_name }} · {{ number_format($line->quantity) }} {{ $line->unit_of_measurement }}</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-3 py-3">
                                    @foreach ($transaction->lines as $line)
                                        <span class="block py-0.5">{{ $line->warehouse_location_name ?: '—' }}</span>
                                    @endforeach
                                </td>
                                <td class="px-3 py-3">
                                    {{ $type === 'in' ? ($transaction->received_by ?: '—') : ($transaction->released_by ?: '—') }}
                                    @if ($type === 'out' && $transaction->received_by)
                                        <span class="mt-1 block text-[10px] text-slate-500">Received by {{ $transaction->received_by }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 print-hidden">
                                    <a href="{{ route('admin.stock-'.$type.'.show', $transaction) }}" class="text-[#2563EB] hover:underline">View / Print</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-10 text-center text-slate-400">No {{ strtolower($transactionLabel) }} transactions match these filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="print-hidden border-t border-[#E5E7EB] px-3 py-2">{{ $transactions->links() }}</div>
            @endif
        </section>
    </div>
@endsection
