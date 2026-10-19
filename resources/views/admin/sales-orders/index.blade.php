@extends('layouts.admin')

@section('title', 'ADS Sales Order System | Globaltronics ICS')

@section('content')
<div class="space-y-6">

    <!-- Top Header Card -->
    <div class="glass-panel bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-200">
                        LED ADVERTISING WORKFLOW
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200">
                        {{ $completeCount }} COMPLETE
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-200">
                        {{ $inProgressCount }} IN PROGRESS
                    </span>
                    @if ($declinedCount > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-200">
                            {{ $declinedCount }} DECLINED
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span>ADS Sales Order System</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl leading-relaxed">
                    End-to-end digital LED advertising workflow: Advertising Sales Orders (ASO), Job Orders (SJO), Content review, IT LED deployment, and Accounting verification compliance.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @can('create', App\Models\SalesOrder::class)
                    <a href="{{ route('admin.sales-orders.create') }}" 
                       class="h-11 px-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 hover:scale-[1.02]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>File New ASO Form</span>
                    </a>
                @endcan

                @can('viewReports', App\Models\SalesOrder::class)
                    <a href="{{ route('admin.sales-orders.reports') }}" 
                       class="h-11 px-4 inline-flex items-center gap-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition-colors">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Financial Reports</span>
                    </a>
                @endcan

                @can('manageRoles', App\Models\SalesOrder::class)
                    <a href="{{ route('admin.sales-orders.roles') }}" 
                       class="h-11 px-4 inline-flex items-center gap-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 text-xs font-bold transition-colors">
                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Assign Roles</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Telemetry KPI Cards -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-6 border-t border-slate-100">
            <!-- Action Queue -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-orange-50 border border-rose-200">
                <span class="text-xs font-bold text-rose-800 uppercase tracking-wider block">My Action Queue</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-rose-950 font-mono-code">{{ $myQueueCount }}</span>
                    <span class="text-xs text-rose-700 font-semibold">Orders Waiting on You</span>
                </div>
            </div>

            <!-- In Progress Pipeline -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200">
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider block">Awaiting Approvals</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-amber-950 font-mono-code">{{ App\Support\Workflow::formatMoney($valueAwaitingApproval) }}</span>
                </div>
                <span class="text-[11px] text-amber-700 font-semibold mt-1 block">{{ $inProgressCount }} active orders in flight</span>
            </div>

            <!-- Approved Contracts -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Approved Contract Value</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-950 font-mono-code">{{ App\Support\Workflow::formatMoney($approvedContractValue) }}</span>
                </div>
                <span class="text-[11px] text-emerald-700 font-semibold mt-1 block">{{ $completeCount }} completed campaigns</span>
            </div>

            <!-- Total System Orders -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200">
                <span class="text-xs font-bold text-blue-800 uppercase tracking-wider block">Total Orders Registered</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-blue-950 font-mono-code">{{ $totalOrders }}</span>
                    <span class="text-xs text-blue-700 font-semibold">ASO Files</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search Bar with Tabs -->
    <div class="glass-panel bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            
            <!-- Queue Tabs (Segmented Switcher) -->
            <div class="p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80 inline-flex items-center gap-1.5 self-start">
                <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['tab' => 'my_queue'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $tab === 'my_queue' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                    <svg class="w-3.5 h-3.5 {{ $tab === 'my_queue' ? 'text-rose-500' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>My Action Queue</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-bold {{ $myQueueCount > 0 ? 'bg-rose-500 text-white animate-pulse' : 'bg-slate-200 text-slate-600' }}">
                        {{ $myQueueCount }}
                    </span>
                </a>

                <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                    <svg class="w-3.5 h-3.5 {{ $tab === 'all' ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>{{ Auth::user()->canViewAllOrders() ? 'All System Orders' : 'My Orders & History' }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-bold {{ $tab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $totalOrders }}
                    </span>
                </a>
            </div>

            <!-- Status Pill Filter Links -->
            <div class="p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80 inline-flex flex-wrap items-center gap-1 self-start lg:self-auto">
                <a href="{{ route('admin.sales-orders.index', array_merge(request()->except('status'), ['tab' => $tab])) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ empty($status) ? 'bg-white text-blue-700 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
                    All Status
                </a>
                <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['status' => App\Support\Workflow::STATUS_IN_PROGRESS, 'tab' => $tab])) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $status === App\Support\Workflow::STATUS_IN_PROGRESS ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === App\Support\Workflow::STATUS_IN_PROGRESS ? 'bg-white' : 'bg-amber-500' }}"></span>
                    <span>In Progress</span>
                </a>
                <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['status' => App\Support\Workflow::STATUS_COMPLETE, 'tab' => $tab])) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $status === App\Support\Workflow::STATUS_COMPLETE ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === App\Support\Workflow::STATUS_COMPLETE ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    <span>Complete</span>
                </a>
                <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['status' => App\Support\Workflow::STATUS_DECLINED, 'tab' => $tab])) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $status === App\Support\Workflow::STATUS_DECLINED ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === App\Support\Workflow::STATUS_DECLINED ? 'bg-white' : 'bg-rose-500' }}"></span>
                    <span>Declined</span>
                </a>
            </div>
        </div>

        <!-- Modern Inline Search and Step Select Filter -->
        <form method="GET" action="{{ route('admin.sales-orders.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif

            <!-- Search Field (Flexible Width) -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search ASO #, SJO #, Client name, BDS name or email..." 
                       class="w-full h-10 pl-10 pr-4 bg-slate-50/80 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400">
            </div>

            <!-- Workflow Step Dropdown -->
            <div class="w-full sm:w-72 shrink-0">
                <select name="step" class="w-full h-10 px-3 bg-slate-50/80 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-slate-700">
                    <option value="">All Workflow Stages</option>
                    @foreach ($workflowSteps as $stepKey => $stepDef)
                        <option value="{{ $stepKey }}" {{ $step === $stepKey ? 'selected' : '' }}>
                            {{ $stepDef['stage'] }} • {{ $stepDef['title'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Inline Action Buttons (No giant full-width button) -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" 
                        class="h-10 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs inline-flex items-center justify-center gap-2 shadow-sm transition-all hover:scale-[1.02]">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filter Orders</span>
                </button>
                @if ($search || $step || $status)
                    <a href="{{ route('admin.sales-orders.index', ['tab' => $tab]) }}" 
                       class="h-10 px-3.5 inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors"
                       title="Reset search & filters">
                        ✕ Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">
                    {{ $tab === 'my_queue' ? 'Action Queue (Orders Awaiting Your Decision)' : 'Sales Orders Ledger' }}
                </h2>
                <span class="text-xs font-mono-code font-bold text-blue-800 bg-blue-100 px-2 py-0.5 rounded-full border border-blue-200">
                    {{ $orders->total() }} Orders
                </span>
            </div>
            <div class="text-[11px] text-slate-400 font-mono-code hidden sm:block">
                Chronological ledger • Sequential workflow state tracking
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-mono-code tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">ASO / SJO #</th>
                        <th class="py-3 px-4">Client / Company</th>
                        <th class="py-3 px-4">BDS</th>
                        <th class="py-3 px-4">Campaign Flight</th>
                        <th class="py-3 px-4 text-right">Total Amount</th>
                        <th class="py-3 px-4">Current Step &amp; Stage</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        @php
                            $stepInfo = $order->step_info;
                            $canAct = $order->canBeActedOnBy(Auth::user());
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $canAct ? 'bg-amber-50/30' : '' }}">
                            <!-- ASO / SJO # -->
                            <td class="py-3.5 px-4 font-mono-code align-top">
                                <a href="{{ route('admin.sales-orders.show', $order) }}" 
                                   class="font-bold text-blue-900 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5 transition-colors">
                                    <span>{{ $order->aso_number }}</span>
                                    <svg class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                                @if ($order->sjo_number)
                                    <div class="mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            SJO: {{ $order->sjo_number }}
                                        </span>
                                    </div>
                                @endif
                                <span class="block text-[10px] text-slate-400 mt-1">Filed: {{ $order->date_filed?->format('M d, Y') }}</span>
                            </td>

                            <!-- Client -->
                            <td class="py-3.5 px-4 align-top max-w-[200px]">
                                <span class="font-bold text-slate-900 block truncate" title="{{ $order->client_name }}">
                                    {{ $order->client_name }}
                                </span>
                                <span class="text-[11px] text-slate-500 block truncate mt-0.5" title="{{ $order->data['led_sites'] ?? 'LED Advertising' }}">
                                    Site: {{ $order->data['led_sites'] ?? 'LED' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono-code">PO: {{ $order->data['po_number'] ?? '—' }}</span>
                            </td>

                            <!-- BDS -->
                            <td class="py-3.5 px-4 align-top">
                                <span class="font-semibold text-slate-800 block">{{ $order->bds_name }}</span>
                                <span class="text-[10px] text-slate-400 block font-mono-code">{{ $order->bds_email }}</span>
                            </td>

                            <!-- Campaign Flight -->
                            <td class="py-3.5 px-4 text-[11px] font-mono-code align-top">
                                <span class="text-slate-800 font-medium block">
                                    {{ $order->start_date ? $order->start_date->format('M d, Y') : '—' }}
                                </span>
                                <span class="text-slate-400 block">to</span>
                                <span class="text-slate-800 font-medium block">
                                    {{ $order->end_date ? $order->end_date->format('M d, Y') : '—' }}
                                </span>
                            </td>

                            <!-- Total Amount -->
                            <td class="py-3.5 px-4 text-right font-mono-code align-top">
                                <span class="font-extrabold text-slate-900 text-sm block">
                                    {{ $order->formatted_amount }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">
                                    {{ $order->data['payment_terms'] ?? 'Standard terms' }}
                                </span>
                            </td>

                            <!-- Current Step -->
                            <td class="py-3.5 px-4 align-top">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200 block w-fit mb-1">
                                    {{ $stepInfo['stage'] }}
                                </span>
                                <span class="font-semibold text-slate-900 block leading-snug">
                                    {{ $stepInfo['title'] }}
                                </span>
                                @if (!empty($stepInfo['roles']))
                                    <span class="text-[10px] text-slate-400 font-mono-code mt-0.5 block">
                                        Assigned: {{ implode(', ', $stepInfo['roles']) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center align-top">
                                @if ($order->isComplete())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Complete</span>
                                    </span>
                                @elseif ($order->isDeclined())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span>Declined</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>In Progress</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="py-3.5 px-4 text-center align-top">
                                <a href="{{ route('admin.sales-orders.show', $order) }}" 
                                   class="h-8 px-3 rounded-lg text-xs font-bold inline-flex items-center gap-1 transition-all shadow-xs {{ $canAct ? 'bg-amber-600 hover:bg-amber-700 text-white animate-pulse' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                    <span>{{ $canAct ? 'Act Now' : 'Open Order' }}</span>
                                    <span>→</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">No Sales Orders in this View</span>
                                    <p class="text-xs text-slate-400 max-w-sm">
                                        @if ($tab === 'my_queue')
                                            You currently have no pending orders requiring your role's sign-off.
                                        @else
                                            No sales orders found matching your search or filters.
                                        @endif
                                    </p>
                                    @can('create', App\Models\SalesOrder::class)
                                        <a href="{{ route('admin.sales-orders.create') }}" class="mt-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-colors">
                                            + File First ASO Form
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
