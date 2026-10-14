@extends('layouts.admin')

@section('title', 'ADS Sales & Financial Reports | Globaltronics ICS')

@section('content')
<div class="space-y-6">

    <!-- Top Header Card -->
    <div class="glass-panel bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200">
                        MANAGEMENT &amp; ACCOUNTING AUDIT
                    </span>
                    <span class="text-xs text-slate-400">• Accrual Basis Reporting</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span>ADS Financial &amp; Operational Reports</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Financial analytics, progress billing vs 10% retention milestones, consolidated monthly campaign amortization schedules, and BDS sales performance.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button type="button" onclick="window.print()" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Report</span>
                </button>

                <a href="{{ route('admin.sales-orders.index') }}" 
                   class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs">
                    <span>Back to Orders</span>
                </a>
            </div>
        </div>

        <!-- High Level Financial KPIs -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-6 border-t border-slate-100">
            <!-- Approved Contract Value -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Approved Contract Value</span>
                <div class="mt-2 text-2xl font-extrabold text-emerald-950 font-mono-code">
                    {{ App\Support\Workflow::formatMoney($approvedContractValue) }}
                </div>
                <span class="text-[11px] text-emerald-700 font-semibold mt-1 block">{{ $completeOrders }} completed orders</span>
            </div>

            <!-- Value Awaiting Approval -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200">
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider block">Value Awaiting Approval</span>
                <div class="mt-2 text-2xl font-extrabold text-amber-950 font-mono-code">
                    {{ App\Support\Workflow::formatMoney($valueAwaitingApproval) }}
                </div>
                <span class="text-[11px] text-amber-700 font-semibold mt-1 block">{{ $inProgressOrders }} in progress pipeline</span>
            </div>

            <!-- Total Pipeline Value -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200">
                <span class="text-xs font-bold text-blue-800 uppercase tracking-wider block">Total Active Contract Base</span>
                <div class="mt-2 text-2xl font-extrabold text-blue-950 font-mono-code">
                    {{ App\Support\Workflow::formatMoney($totalPipelineValue) }}
                </div>
                <span class="text-[11px] text-blue-700 font-semibold mt-1 block">Cost base on PO commitments</span>
            </div>

            <!-- Order Counts Summary -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Total Volume</span>
                <div class="mt-2 text-3xl font-extrabold text-slate-900 font-mono-code">
                    {{ $totalOrders }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $completeOrders }} Done • {{ $inProgressOrders }} Active • {{ $declinedOrders }} Declined</span>
            </div>
        </div>
    </div>

    <!-- ACCRUAL BASIS & MILESTONES (10% Retention Constant) -->
    <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Accrual Basis &amp; Payment Milestones</h2>
            </div>
            <span class="text-xs font-mono-code font-bold bg-cyan-50 text-cyan-900 border border-cyan-200 px-2.5 py-0.5 rounded-full">
                10% Retention Held Constant
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Cost Base on PO Total</span>
                <div class="mt-2 text-2xl font-extrabold text-slate-900 font-mono-code">
                    {{ App\Support\Workflow::formatMoney($totalPipelineValue) }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Total revenue committed across active and completed advertising orders.</p>
            </div>

            <div class="p-5 rounded-2xl bg-blue-50/70 border border-blue-200">
                <span class="text-xs font-bold text-blue-900 uppercase tracking-wider block">Progress Billing ({{ (1 - $retentionRate) * 100 }}%)</span>
                <div class="mt-2 text-2xl font-extrabold text-blue-950 font-mono-code">
                    {{ App\Support\Workflow::formatMoney($totalProgressBillingAmount) }}
                </div>
                <p class="text-xs text-blue-700 mt-1">Billable per broadcast flight milestones, proofing, and monthly flight delivery.</p>
            </div>

            <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200">
                <span class="text-xs font-bold text-amber-900 uppercase tracking-wider block">Retention Held ({{ $retentionRate * 100 }}%)</span>
                <div class="mt-2 text-2xl font-extrabold text-amber-950 font-mono-code">
                    {{ App\Support\Workflow::formatMoney($totalRetentionAmount) }}
                </div>
                <p class="text-xs text-amber-700 mt-1">Held until full campaign completion, certificate of performance, and final reconciliation.</p>
            </div>
        </div>
    </div>

    <!-- TWO-COLUMN SECTION: Orders per Step & BDS Sales -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ORDERS PER STEP (6 cols) -->
        <div class="lg:col-span-6 glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Orders per Workflow Step</h2>
                </div>
                <span class="text-[11px] font-mono-code text-slate-400">Pipeline Distribution</span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($ordersPerStep as $stepKey => $data)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-mono-code font-bold uppercase text-slate-400 block">{{ $data['stage'] }}</span>
                            <span class="text-xs font-bold text-slate-800 truncate block">{{ $data['title'] }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-mono-code font-bold text-slate-900 block">{{ $data['count'] }} orders</span>
                            <span class="text-[11px] font-mono-code text-slate-500 block">{{ App\Support\Workflow::formatMoney($data['value']) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SALES BY BDS (6 cols) -->
        <div class="lg:col-span-6 glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Sales by BDS (Account Executives)</h2>
                </div>
                <span class="text-[11px] font-mono-code text-slate-400">{{ $salesByBds->count() }} Reps</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-mono-code text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3">BDS Name &amp; Email</th>
                            <th class="py-3 px-3 text-center">Orders</th>
                            <th class="py-3 px-3 text-right">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($salesByBds as $bds)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 block">{{ $bds->bds_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono-code block">{{ $bds->bds_email }}</span>
                                </td>
                                <td class="py-3 px-3 text-center font-mono-code font-bold text-slate-800">
                                    {{ $bds->order_count }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono-code font-extrabold text-emerald-700">
                                    {{ App\Support\Workflow::formatMoney($bds->total_sales) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">No BDS sales records logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- CONSOLIDATED MONTHLY AMORTIZATION SCHEDULE -->
    <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Consolidated Monthly Campaign Amortization Schedule</h2>
            </div>
            <span class="text-xs font-mono-code text-slate-400">Even revenue spread across flight months</span>
        </div>

        @if (!empty($monthlyAmortization))
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-mono-code tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Calendar Month</th>
                            <th class="py-3 px-4 text-center">Active Campaigns</th>
                            <th class="py-3 px-4 text-right">Recognized Revenue (₱)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono-code">
                        @foreach ($monthlyAmortization as $monthLabel => $mData)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $monthLabel }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-blue-700">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-50 border border-blue-200">
                                        {{ $mData['order_count'] }} Campaigns
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-emerald-700 text-sm">
                                    {{ App\Support\Workflow::formatMoney($mData['total_amount']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-slate-400 italic py-4">No active campaign flight schedules logged to generate monthly amortization breakdown.</p>
        @endif
    </div>

</div>
@endsection
