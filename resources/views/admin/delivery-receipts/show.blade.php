@extends('layouts.operations-dashboard')

@section('title', 'Delivery Receipt '.$receipt->number)
@section('breadcrumb', 'Logistics / Delivery Receipts / '.$receipt->number)

@section('content')
    <style>
        .receipt-document { background-color: white !important; color: #111; color-scheme: light; font-family: Arial, sans-serif; }
        .receipt-document table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .receipt-document th, .receipt-document td { border: 1px solid #444; overflow-wrap: anywhere; padding: 5px; text-align: left; vertical-align: top; }
        .receipt-document thead { display: table-header-group; }
        .receipt-document tr, .receipt-signatures { break-inside: avoid; }
        @media print {
            #dashboardSidebar, #dashboardSidebarBackdrop, header, .receipt-controls, main > div:first-child { display: none !important; }
            body, main { background: white !important; color: black !important; }
            .receipt-document { border: 0 !important; box-shadow: none !important; max-width: none !important; padding: 0 !important; }
        }
    </style>
    <div class="mx-auto max-w-5xl space-y-4 px-3 py-4 sm:px-5">
        <div class="receipt-controls space-y-3">
            @if ($errors->any())
                <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-[20px] font-semibold">Delivery Receipt {{ $receipt->number }}</h1>
                    <p class="text-[12px] text-slate-500">{{ ucfirst($receipt->type) }} · {{ ucfirst($receipt->status) }}</p>
                    <p class="text-[11px] text-slate-500">Created {{ $receipt->created_at->format('Y-m-d H:i') }} by {{ $receipt->creator?->name ?: 'Unknown user' }}
                        @if ($receipt->released_at)
                            · Released {{ $receipt->released_at->format('Y-m-d H:i') }} by {{ $receipt->releaser?->name ?: 'Unknown user' }}
                        @endif
                    </p>
                    @if ($receipt->stockTransaction)
                        <a class="text-[12px] text-[#2563EB] underline" href="{{ route('admin.stock-out.show', $receipt->stockTransaction) }}">Stock Out {{ $receipt->stockTransaction->reference_number }}</a>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.delivery-receipts.index') }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">Back</a>
                    <button type="button" onclick="window.print()" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">Print</button>
                    @if ($receipt->status === 'draft')
                        <a href="{{ route('admin.delivery-receipts.edit', $receipt) }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px]">Edit Draft</a>
                        <form method="POST" action="{{ route('admin.delivery-receipts.release', $receipt) }}" onsubmit="return confirm('Release this receipt, deduct its quantities from inventory, and create a Stock Out? Do not create another Stock Out for this delivery.')">
                            @csrf
                            <button type="submit" class="rounded-sm bg-[#2563EB] px-3 py-2 text-[12px] font-medium text-white">Release &amp; Deduct Stock</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <article class="receipt-document space-y-4 border border-slate-300 bg-white p-5 text-[12px] shadow-sm sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-black pb-3">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/logo/globaltronics_logo.png') }}" alt="" class="h-12 w-12 object-contain">
                    <div>
                        <p class="text-[20px] font-bold uppercase">Globaltronics</p>
                        <p class="text-[9px] uppercase">Leading Digital Innovation</p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-[20px] font-bold uppercase">Delivery Receipt</h2>
                    <p class="mt-1 font-semibold">{{ $receipt->type === 'client' ? 'DR to Client' : 'DR Internal' }}</p>
                    <p class="mt-1">DR No.: <strong>{{ $receipt->number }}</strong></p>
                </div>
            </div>
            @if ($receipt->status === 'draft')
                <p class="border border-black p-2 text-center font-bold uppercase">Draft — not released</p>
            @endif
            <div class="grid gap-x-6 gap-y-2 sm:grid-cols-2">
                <p><strong>{{ $receipt->type === 'client' ? 'Client' : 'Site / Client' }}:</strong> {{ $receipt->recipient }}</p>
                <p><strong>Date:</strong> {{ $receipt->receipt_date->format('Y-m-d') }}</p>
                <p><strong>Address:</strong> {{ $receipt->address ?: '____________________________' }}</p>
                <p><strong>PO No.:</strong> {{ $receipt->purchase_order_number ?: '________________' }}</p>
                @if ($receipt->type === 'client')
                    <p><strong>TIN:</strong> {{ $receipt->tin ?: '____________________________' }}</p>
                    <p><strong>Terms:</strong> {{ $receipt->terms ?: '________________' }}</p>
                    <p><strong>Contact Person:</strong> {{ $receipt->contact_person ?: '____________________________' }}</p>
                    <p><strong>SO No.:</strong> {{ $receipt->sales_order_number ?: '________________' }}</p>
                @endif
                <p><strong>{{ $receipt->type === 'client' ? 'SRF No.' : 'SR No.' }}:</strong> {{ $receipt->request_number ?: '________________' }}</p>
            </div>
            <table>
                <colgroup>
                    @if ($receipt->type === 'internal')
                        <col style="width: 9%"><col style="width: 9%"><col style="width: 8%"><col style="width: 49%"><col style="width: 25%">
                    @else
                        <col style="width: 10%"><col style="width: 12%"><col style="width: 78%">
                    @endif
                </colgroup>
                <thead>
                    <tr>
                        @if ($receipt->type === 'internal') <th>Item No.</th> @endif
                        <th>Qty</th>
                        <th>{{ $receipt->type === 'client' ? 'Unit' : 'UOM' }}</th>
                        <th>{{ $receipt->type === 'client' ? 'Item Name / Description' : 'Description' }}</th>
                        @if ($receipt->type === 'internal') <th>Model / Serial No.</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->lines as $line)
                        <tr>
                            @if ($receipt->type === 'internal') <td>{{ $loop->iteration }}</td> @endif
                            <td>{{ number_format($line->quantity) }}</td>
                            <td>{{ $line->unit_of_measurement }}</td>
                            <td>{{ $line->product_name }}@if ($receipt->type === 'client' && $line->model_serial) — {{ $line->model_serial }} @endif</td>
                            @if ($receipt->type === 'internal') <td>{{ $line->model_serial ?: '—' }}</td> @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="min-h-16 border border-slate-600 p-2">
                <strong>{{ $receipt->type === 'client' ? 'Special Instructions / Remarks' : 'Remarks' }}:</strong>
                <p class="whitespace-pre-line">{{ $receipt->remarks }}</p>
            </div>
            @if ($receipt->type === 'client')
                <p>Received the above items in good order and condition.</p>
            @endif
            <div class="receipt-signatures grid grid-cols-2 gap-5 pt-4 text-[10px] sm:grid-cols-5">
                @foreach (['Prepared by', 'Checked by', 'Approved by', 'Released by', 'Received by'] as $label)
                    <div class="min-w-0">
                        <div class="h-8 border-b border-black"></div>
                        <p class="mt-1">{{ $label }}{{ $label === 'Received by' && $receipt->received_by ? ': '.$receipt->received_by : '' }}</p>
                    </div>
                @endforeach
            </div>
            @if ($receipt->type === 'client')
                <p class="text-[9px]">Operational delivery receipt — not a tax invoice.</p>
            @endif
        </article>
    </div>
@endsection
