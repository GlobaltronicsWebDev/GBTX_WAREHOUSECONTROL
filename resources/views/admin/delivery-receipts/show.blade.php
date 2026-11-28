@extends('layouts.operations-dashboard')

@section('title', 'Delivery Receipt '.$receipt->number)
@section('breadcrumb', 'Logistics / Delivery Receipts / '.$receipt->number)

@section('content')
    <style>
        .receipt-scroll { overflow-x: auto; }
        .receipt-document { min-width: 660px; background-color: white !important; color: #111; color-scheme: light; font-family: Arial, sans-serif; font-size: 11px; }
        .receipt-document * { box-sizing: border-box; }
        .receipt-brand { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .receipt-brand img { width: 50px; height: 50px; object-fit: contain; filter: grayscale(1); }
        .receipt-brand-name { border-bottom: 1px solid #111; font-size: 25px; font-style: italic; font-weight: 900; line-height: 1; }
        .receipt-tagline { font-size: 9px; letter-spacing: 1px; text-align: center; text-transform: uppercase; }
        .receipt-heading { margin: 8px 0 0; font-size: 19px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .receipt-top { display: grid; grid-template-columns: 3fr 2fr; gap: 16px; }
        .receipt-client-heading { border: 1px solid #111; padding: 8px; }
        .receipt-client-heading .receipt-heading { margin: 0 0 6px; }
        .receipt-number { margin: 5px 0 12px; font-size: 14px; text-align: center; }
        .receipt-fields { display: grid; gap: 5px; }
        .receipt-client-fields { margin-top: 20px; }
        .receipt-internal-fields { display: grid; grid-template-columns: 3fr 2fr; gap: 20px; margin-top: 8px; }
        .receipt-field { display: flex; align-items: baseline; gap: 5px; }
        .receipt-field strong { flex: none; text-transform: uppercase; }
        .receipt-field span { flex: 1; min-width: 0; min-height: 15px; border-bottom: 1px solid #555; overflow-wrap: anywhere; }
        .receipt-draft { margin: 8px 0; border: 1px solid #111; padding: 4px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .receipt-document table { width: 100%; margin-top: 10px; border-collapse: collapse; table-layout: fixed; }
        .receipt-document th, .receipt-document td { border: 1px solid #444; overflow-wrap: anywhere; padding: 2px 4px; text-align: left; vertical-align: top; }
        .receipt-document th { font-size: 10px; text-align: center; text-transform: uppercase; }
        .receipt-document td { height: 18px; }
        .receipt-document thead { display: table-header-group; }
        .receipt-document tr, .receipt-footer, .receipt-signatures { break-inside: avoid; }
        .receipt-footer { margin-top: 10px; }
        .receipt-client-footer { display: grid; grid-template-columns: 3fr 2fr; gap: 16px; }
        .receipt-remarks { min-height: 50px; border: 1px solid #555; padding: 5px; }
        .receipt-remarks p { white-space: pre-line; overflow-wrap: anywhere; }
        .receipt-signatures { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; border: 1px solid #555; padding: 6px; font-size: 10px; }
        .receipt-client-signatures { grid-template-columns: repeat(4, minmax(0, 1fr)); margin-top: 10px; }
        .receipt-internal-signatures { margin-top: 10px; }
        .receipt-signature-line { min-height: 28px; border-bottom: 1px solid #555; overflow-wrap: anywhere; }
        .receipt-received { display: flex; min-height: 125px; flex-direction: column; justify-content: space-between; border: 1px solid #555; padding: 8px; text-align: center; }
        .receipt-received-line { border-bottom: 1px solid #555; min-height: 22px; overflow-wrap: anywhere; }
        .receipt-note { margin-top: 5px; font-size: 9px; }
        @media print {
            @page { margin: 8mm; }
            #dashboardSidebar, #dashboardSidebarBackdrop, header, .receipt-controls, main > div:first-child { display: none !important; }
            body, main { background: white !important; color: black !important; }
            .receipt-scroll { overflow: visible; }
            .receipt-document { min-width: 0; border: 0 !important; box-shadow: none !important; max-width: none !important; padding: 0 !important; }
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
                    <p class="text-[12px] text-slate-500">{{ $receipt->type === 'client' ? 'DR to Client' : 'DR Internal' }} · {{ ucfirst($receipt->status) }}</p>
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

        <div class="receipt-scroll">
        <article class="receipt-document border border-slate-300 bg-white p-5 shadow-sm sm:p-8">
            @if ($receipt->type === 'client')
                <div class="receipt-top">
                    <div>
                        <div class="receipt-brand">
                            <img src="{{ asset('assets/images/logo/globaltronics_logo.png') }}" alt="">
                            <div>
                                <p class="receipt-brand-name">GLOBALTRONICS</p>
                                <p class="receipt-tagline">LED By Vision, Build By Trust</p>
                            </div>
                        </div>
                        <div class="receipt-fields receipt-client-fields">
                            <div class="receipt-field"><strong>Client:</strong><span>{{ $receipt->recipient }}</span></div>
                            <div class="receipt-field"><strong>TIN:</strong><span>{{ $receipt->tin }}</span></div>
                            <div class="receipt-field"><strong>Address:</strong><span>{{ $receipt->address }}</span></div>
                            <div class="receipt-field"><strong>Contact Person:</strong><span>{{ $receipt->contact_person }}</span></div>
                        </div>
                    </div>
                    <div class="receipt-client-heading">
                        <h2 class="receipt-heading">Delivery Receipt</h2>
                        <p class="receipt-number">No. <strong>{{ $receipt->number }}</strong></p>
                        <div class="receipt-fields">
                            <div class="receipt-field"><strong>Date:</strong><span>{{ $receipt->receipt_date->format('Y-m-d') }}</span></div>
                            <div class="receipt-field"><strong>Terms:</strong><span>{{ $receipt->terms }}</span></div>
                            <div class="receipt-field"><strong>PO No.:</strong><span>{{ $receipt->purchase_order_number }}</span></div>
                            <div class="receipt-field"><strong>SO No.:</strong><span>{{ $receipt->sales_order_number }}</span></div>
                            <div class="receipt-field"><strong>SRF No.:</strong><span>{{ $receipt->request_number }}</span></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="receipt-brand">
                    <img src="{{ asset('assets/images/logo/globaltronics_logo.png') }}" alt="">
                    <div>
                        <p class="receipt-brand-name">GLOBALTRONICS</p>
                        <p class="receipt-tagline">Leading Digital Innovation</p>
                    </div>
                </div>
                <h2 class="receipt-heading">Delivery Receipt</h2>
                <div class="receipt-internal-fields">
                    <div class="receipt-fields">
                        <div class="receipt-field"><strong>SR No.:</strong><span>{{ $receipt->request_number }}</span></div>
                        <div class="receipt-field"><strong>Site / Client:</strong><span>{{ $receipt->recipient }}</span></div>
                        <div class="receipt-field"><strong>Address:</strong><span>{{ $receipt->address }}</span></div>
                    </div>
                    <div class="receipt-fields">
                        <div class="receipt-field"><strong>DR No.:</strong><span>{{ $receipt->number }}</span></div>
                        <div class="receipt-field"><strong>PO No.:</strong><span>{{ $receipt->purchase_order_number }}</span></div>
                        <div class="receipt-field"><strong>Date:</strong><span>{{ $receipt->receipt_date->format('Y-m-d') }}</span></div>
                    </div>
                </div>
            @endif
            @if ($receipt->status === 'draft')
                <p class="receipt-draft">Draft — not released</p>
            @endif
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
                    @for ($row = $receipt->lines->count(); $row < 24; $row++)
                        <tr aria-hidden="true">
                            @if ($receipt->type === 'internal') <td>&nbsp;</td> @endif
                            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                            @if ($receipt->type === 'internal') <td>&nbsp;</td> @endif
                        </tr>
                    @endfor
                </tbody>
            </table>
            @if ($receipt->type === 'client')
                <div class="receipt-footer receipt-client-footer">
                    <div>
                        <div class="receipt-remarks"><strong>Special Instructions / Remarks:</strong><p>{{ $receipt->remarks }}</p></div>
                        <div class="receipt-signatures receipt-client-signatures">
                            @foreach (['Prepared by', 'Checked by', 'Released by', 'Approved by'] as $label)
                                <div><strong>{{ $label }}:</strong><div class="receipt-signature-line"></div></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="receipt-received">
                        <p>Received the above items in good order and condition.</p>
                        <div><div class="receipt-received-line">{{ $receipt->received_by }}</div><strong>Signature over printed name</strong></div>
                        <div><div class="receipt-received-line"></div><strong>Date</strong></div>
                    </div>
                </div>
                <p class="receipt-note">Operational delivery receipt — not a tax invoice.</p>
            @else
                <div class="receipt-footer">
                    <div class="receipt-remarks"><strong>Remarks:</strong><p>{{ $receipt->remarks }}</p></div>
                    <div class="receipt-signatures receipt-internal-signatures">
                        @foreach (['Prepared by', 'Checked by', 'Approved by', 'Released by', 'Received by'] as $label)
                            <div><strong>{{ $label }}:</strong><div class="receipt-signature-line">{{ $label === 'Received by' ? $receipt->received_by : '' }}</div></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
        </div>
    </div>
@endsection
