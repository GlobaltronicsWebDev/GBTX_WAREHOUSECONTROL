@extends('layouts.admin')

@section('title', 'Approved Stock Requisition Form (SRF) Summary | Globaltronics ICS')

@section('content')
<div class="space-y-6">
    
    <!-- Top Header Card -->
    <div class="glass-panel bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                            OFFICIAL VERIFIED REQUISITIONS
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                            {{ $totalApprovedCount }} APPROVED SRFs
                        </span>
                        <span class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold ml-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            WAREHOUSE CLEARANCE VERIFIED
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                        <span>Approved SRF Summary</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl leading-relaxed">
                        Official log of verified Stock Requisition Forms (SRF) cleared by Warehouse Management for project staging, technical bench testing, and site deployment.
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2 self-start lg:self-auto shrink-0">
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Telemetry Metric Cards -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-slate-100">
                <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Total Approved SRFs</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-emerald-950 font-mono-code">{{ $totalApprovedCount }}</span>
                        <span class="text-xs text-emerald-700 font-semibold">Requisitions Cleared</span>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200">
                    <span class="text-xs font-bold text-blue-800 uppercase tracking-wider block">Total Units Released</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-blue-950 font-mono-code">{{ number_format($totalApprovedUnits) }}</span>
                        <span class="text-xs text-blue-700 font-semibold">Hardware Units Staged</span>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-cyan-50/60 border border-cyan-200">
                    <span class="text-xs font-bold text-cyan-800 uppercase tracking-wider block">Compliance Audit</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-cyan-950 font-mono-code">100%</span>
                        <span class="text-xs text-cyan-700 font-semibold">Signatories Verified</span>
                    </div>
                </div>
            </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="glass-panel bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('admin.srf.approved') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search ?? '' }}" 
                    placeholder="Search approved SRF #, SSO #, Client, PO, Verifier, or hardware model..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:bg-white focus:ring-2 focus:ring-emerald-500"
                >
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors"
                >
                    Filter Log
                </button>
                @if (!empty($search))
                    <a 
                        href="{{ route('admin.srf.approved') }}" 
                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors whitespace-nowrap"
                    >
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Approved SRF Summary Table (Combined Items) -->
    <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Approved Requisitions Ledger</h3>
                <span class="text-xs font-mono-code font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                    {{ $groupedApproved->count() }} Requisitions
                </span>
            </div>
            <div class="text-[11px] text-slate-400 font-mono-code">
                Combined multi-item view • Grouped by SRF #
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-mono-code tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">SRF #</th>
                        <th class="py-3 px-4">SSO #</th>
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">PO #</th>
                        <th class="py-3 px-4">Date / Needed</th>
                        <th class="py-3 px-4 min-w-[200px]">ITEM &amp; QTY</th>
                        <th class="py-3 px-4 min-w-[180px]">Remarks</th>
                        <th class="py-3 px-4 min-w-[140px]">Availability</th>
                        <th class="py-3 px-4">Prepared By</th>
                        <th class="py-3 px-4">Approved &amp; Verified By</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center min-w-[130px]">Document</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($groupedApproved as $srfNumber => $items)
                        @php
                            $first = $items->first();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- SRF # -->
                            <td class="py-3.5 px-4 font-mono-code align-top">
                                <a 
                                    href="{{ route('admin.srf.pdf', urlencode($first->srf_number)) }}" 
                                    target="_blank"
                                    class="font-bold text-cyan-950 bg-cyan-50 hover:bg-cyan-100 border border-cyan-200 px-2 py-1 rounded-lg inline-flex items-center gap-1 text-xs transition-colors"
                                    title="Open Stock Requisition Form PDF"
                                >
                                    <span>{{ $first->srf_number }}</span>
                                    <svg class="w-3 h-3 text-cyan-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                                @if ($items->count() > 1)
                                    <span class="block text-[10px] font-mono-code text-cyan-700 mt-1 font-bold">
                                        {{ $items->count() }} items combined
                                    </span>
                                @endif
                            </td>

                            <!-- SSO # -->
                            <td class="py-3.5 px-4 font-mono-code align-top">
                                <span class="font-semibold text-blue-950 bg-blue-50 border border-blue-200 px-2 py-1 rounded-lg inline-block text-xs">
                                    {{ $first->sso_number }}
                                </span>
                            </td>

                            <!-- Client -->
                            <td class="py-3.5 px-4 align-top max-w-[160px]">
                                <span class="font-bold text-slate-900 block truncate" title="{{ $first->client ?? $first->project_name }}">
                                    {{ $first->client ?? $first->project_name }}
                                </span>
                                @if ($first->client && $first->project_name && $first->client !== $first->project_name)
                                    <span class="text-[10px] text-slate-500 block truncate mt-0.5" title="{{ $first->project_name }}">
                                        Prj: {{ $first->project_name }}
                                    </span>
                                @endif
                            </td>

                            <!-- PO # -->
                            <td class="py-3.5 px-4 font-mono-code align-top">
                                @if ($first->po_number)
                                    <span class="text-[11px] text-cyan-900 font-bold bg-cyan-50 px-2 py-1 rounded-lg border border-cyan-200 inline-block">
                                        {{ $first->po_number }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono-code text-xs">—</span>
                                @endif
                            </td>

                            <!-- Date / Needed -->
                            <td class="py-3.5 px-4 text-[11px] font-mono-code align-top">
                                <span class="text-slate-700 block">Filed: {{ $first->requisition_date?->format('M d, Y') ?? $first->created_at->format('M d, Y') }}</span>
                                @if ($first->date_needed)
                                    <span class="text-amber-700 font-semibold block mt-0.5">Needed: {{ $first->date_needed->format('M d, Y') }}</span>
                                @endif
                            </td>

                            <!-- COMBINED ITEM & QTY -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="space-y-2">
                                    @foreach ($items as $item)
                                        <div class="flex items-center gap-2 {{ !$loop->last ? 'pb-2 border-b border-slate-100' : '' }}">
                                            <span class="shrink-0 font-mono-code text-[11px] text-blue-700 font-bold bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-lg inline-flex items-center gap-1 shadow-2xs">
                                                <span>{{ $item->quantity }}</span>
                                                <span class="text-[10px] text-blue-500 font-semibold">{{ $item->uom ?? 'PCS' }}</span>
                                            </span>
                                            <span class="font-semibold text-slate-800 block truncate flex-1 min-w-0" title="{{ $item->inventoryItem?->model ?? 'Custom Hardware' }}">
                                                {{ $item->inventoryItem?->model ?? 'Custom Hardware' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <!-- COMBINED REMARKS -->
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                <div class="space-y-2">
                                    @foreach ($items as $item)
                                        <div class="{{ !$loop->last ? 'pb-2 border-b border-slate-100' : '' }}">
                                            <span class="text-xs text-slate-600 block py-0.5 truncate" title="{{ $item->remarks ?? 'None' }}">
                                                {{ $item->remarks ?? '—' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <!-- COMBINED AVAILABILITY -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="space-y-2">
                                    @foreach ($items as $item)
                                        <div class="{{ !$loop->last ? 'pb-2 border-b border-slate-100' : '' }}">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>IN STOCK</span>
                                            </span>
                                            <span class="block text-[10px] text-slate-500 font-mono-code mt-0.5">STAGE-BAY-01</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Prepared By -->
                            <td class="py-3.5 px-4 font-medium text-slate-700 align-top">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                    <span>{{ $first->prepared_by }}</span>
                                </div>
                            </td>

                            <!-- Approved & Verified By -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="space-y-0.5">
                                    <span class="font-bold text-slate-900 block text-xs">
                                        {{ $first->verified_by_name ?? 'Warehouse Management' }}
                                    </span>
                                    <span class="text-[11px] font-mono-code text-emerald-700 block">
                                        {{ $first->verified_at?->format('M d, Y • h:i A') ?? 'Verified' }}
                                    </span>
                                    @if ($first->verification_notes)
                                        <span class="text-[10px] text-slate-400 italic block line-clamp-1" title="{{ $first->verification_notes }}">
                                            "{{ $first->verification_notes }}"
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 align-top">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>APPROVED</span>
                                </span>
                            </td>

                            <!-- Document / Download PDF -->
                            <td class="py-3.5 px-4 text-center align-top">
                                <a 
                                    href="{{ route('admin.srf.pdf', urlencode($first->srf_number)) }}" 
                                    target="_blank"
                                    class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold inline-flex items-center gap-1.5 shadow-xs transition-all hover:scale-105"
                                    title="Download & Print Official Stock Requisition Form PDF"
                                >
                                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span>Download PDF</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-16 text-center text-slate-400">te-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">No Approved SRFs Found</span>
                                    <p class="text-xs text-slate-400 max-w-sm">
                                        @if (!empty($search))
                                            No verified requisitions matched your search "{{ $search }}". Try clearing your search filter.
                                        @else
                                            No SRF requisitions have been verified and approved yet. When Warehouse Admin verifies a pending SRF, it will appear here in this official ledger.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
