@extends('layouts.operations-dashboard')

@section('title', 'Transaction Listing')
@section('breadcrumb', 'Reports / Transactions')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="-mx-3 flex items-center gap-2 border-y border-[#E5E7EB] px-3 py-2.5 text-[10px] uppercase tracking-wide text-slate-500 sm:-mx-5 sm:px-5" aria-label="Report date and time">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="1"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
            </svg>
            <time id="dashboardDate" class="whitespace-nowrap">Loading date</time>
            <span class="text-slate-300">|</span>
            <time id="dashboardClock" class="tabular-nums whitespace-nowrap font-mono text-[11px] font-medium text-slate-700">--:--:--</time>
        </section>

        <section class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-[18px] font-semibold text-slate-900">Transaction Listing Reports</h1>
                <p class="mt-1 text-[11px] text-slate-500">Stock Requisition Form records currently stored in the application.</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="text-[11px] text-slate-600 underline">Back to Reports</a>
        </section>

        <section class="border border-[#E5E7EB] bg-white">
            <form method="GET" action="{{ route('admin.reports.transactions') }}" class="flex flex-col gap-2 border-b border-[#E5E7EB] px-3 py-2.5 lg:flex-row lg:items-center">
                <label class="sr-only" for="transactionSearch">Search SRF transactions</label>
                <input id="transactionSearch" type="search" name="search" value="{{ $search }}" placeholder="Search reference, project, or item" class="h-8 w-full border border-[#E5E7EB] px-2 text-[11px] outline-none focus:border-[#2563EB] lg:max-w-xs">
                <label class="sr-only" for="transactionStatus">Transaction status</label>
                <select id="transactionStatus" name="status" class="h-8 border border-[#E5E7EB] bg-white px-2 text-[11px] outline-none focus:border-[#2563EB]">
                    <option value="">All statuses</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="for_approval" @selected($status === 'for_approval')>For Approval</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="verified" @selected($status === 'verified')>Verified</option>
                    <option value="declined" @selected($status === 'declined')>Declined</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                    <option value="on_hold" @selected($status === 'on_hold')>On Hold</option>
                </select>
                <button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Apply</button>
                @if ($search !== '' || $status !== '')
                    <a href="{{ route('admin.reports.transactions') }}" class="text-[10px] text-slate-500 underline">Clear filters</a>
                @endif
                <span class="text-[10px] text-slate-500 lg:ml-auto">{{ number_format($transactions->total()) }} line items</span>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-left text-[11px]">
                    <thead class="h-9 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium text-slate-500">
                        <tr>
                            <th class="px-3">Reference</th>
                            <th class="px-3">Project / Client</th>
                            <th class="px-3">Item / Description</th>
                            <th class="px-3 text-right">Qty</th>
                            <th class="px-3">Status</th>
                            <th class="px-3">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($transactions as $transaction)
                            @php
                                $statusLabel = match ($transaction->status) {
                                    'approved', 'completed', 'verified' => 'Approved',
                                    'for_approval' => 'For Approval',
                                    'on_hold' => 'On Hold',
                                    'declined', 'rejected' => 'Declined',
                                    default => 'Pending',
                                };
                            @endphp
                            <tr class="h-11 text-slate-700">
                                <td class="whitespace-nowrap px-3 font-mono text-[10px] tabular-nums">
                                    <a href="{{ route('admin.srf.pdf', $transaction->srf_number) }}" class="text-[#2563EB] hover:underline">{{ $transaction->srf_number }}</a>
                                </td>
                                <td class="max-w-48 truncate px-3">{{ $transaction->client ?: $transaction->project_name }}</td>
                                <td class="max-w-64 truncate px-3">{{ $transaction->item_model }}@if ($transaction->item_description)<span class="text-slate-400"> · {{ $transaction->item_description }}</span>@endif</td>
                                <td class="whitespace-nowrap px-3 text-right tabular-nums">{{ number_format($transaction->quantity) }} {{ $transaction->uom ?: 'PCS' }}</td>
                                <td class="px-3"><span class="whitespace-nowrap border border-[#E5E7EB] bg-[#F8F9FA] px-1.5 py-0.5 text-[10px]">{{ $statusLabel }}</span></td>
                                <td class="whitespace-nowrap px-3 font-mono text-[10px] tabular-nums">{{ $transaction->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-[11px] text-slate-500">No SRF transaction records match these filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="border-t border-[#E5E7EB] px-3 py-2">{{ $transactions->links() }}</div>
            @endif
        </section>
    </div>
@endsection
