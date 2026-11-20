@extends('layouts.operations-dashboard')

@section('title', 'Reports')
@section('breadcrumb', 'Reports')

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

        <section class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-4" aria-label="Available reports">
            <article class="flex min-h-32 flex-col border border-[#E5E7EB] bg-white p-4">
                <h1 class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Stock Movement Reports</h1>
                <p class="mt-2 font-mono text-[25px] font-semibold leading-7 tabular-nums text-slate-800">—</p>
                <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                    <span class="text-[10px] text-slate-500">Inventory movement is not tracked</span>
                    <span class="text-[10px] text-slate-400">Unavailable</span>
                </div>
            </article>
            <article class="flex min-h-32 flex-col border border-[#E5E7EB] bg-white p-4">
                <h2 class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Transaction Listing Reports</h2>
                <p data-report-count="{{ $srfCount }}" class="mt-2 font-mono text-[25px] font-semibold leading-7 tabular-nums text-slate-800">{{ number_format($srfCount) }}</p>
                <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                    <span class="text-[10px] text-slate-500">Live SRF records</span>
                    <a href="{{ route('admin.dashboard') }}#transactionsHeading" class="text-[10px] font-medium text-[#2563EB] hover:underline">View <span aria-hidden="true">›</span></a>
                </div>
            </article>
            <article class="flex min-h-32 flex-col border border-[#E5E7EB] bg-white p-4">
                <h2 class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Inventory Stock List Reports</h2>
                <p class="mt-2 font-mono text-[25px] font-semibold leading-7 tabular-nums text-slate-800">—</p>
                <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                    <span class="text-[10px] text-slate-500">Inventory records are not tracked</span>
                    <span class="text-[10px] text-slate-400">Unavailable</span>
                </div>
            </article>
            <article class="flex min-h-32 flex-col border border-[#E5E7EB] bg-white p-4">
                <h2 class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Inventory Aging Reports</h2>
                <p class="mt-2 font-mono text-[25px] font-semibold leading-7 tabular-nums text-slate-800">—</p>
                <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                    <span class="text-[10px] text-slate-500">Inventory records are not tracked</span>
                    <span class="text-[10px] text-slate-400">Unavailable</span>
                </div>
            </article>
        </section>
    </div>
@endsection
