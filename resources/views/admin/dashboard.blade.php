@extends('layouts.admin')

@section('title', 'Warehouse Operations Dashboard | Globaltronics ICS')

@section('content')
<!-- Page Header: Warehouse Operations Dashboard -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-3 border-b border-slate-200">
    <div>
        <div class="flex items-center gap-2 text-xs font-mono-code text-blue-600 mb-1">
            @if (Auth::user()->isItAdmin())
                <span>TERMINAL ID: IT-ADM-01</span>
                <span>•</span>
                <span class="text-emerald-600 font-semibold">SECURITY &amp; ACCESS GOVERNANCE</span>
            @elseif (Auth::user()->isTechnical())
                <span>TERMINAL ID: WMS-TECH-01</span>
                <span>•</span>
                <span class="text-cyan-600 font-semibold">TECHNICAL &amp; SRF REQUISITIONS</span>
            @else
                <span>TERMINAL ID: WMS-ADM-01</span>
                <span>•</span>
                <span class="text-emerald-600 font-semibold">FACILITY ACTIVE</span>
            @endif
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            {{ Auth::user()->isItAdmin() ? 'IT Administration & Account Management' : (Auth::user()->isTechnical() ? 'Technical Operations & SRF Requisitions' : 'Warehouse Operations Dashboard') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            @if (Auth::user()->isItAdmin())
                Globaltronics Identity &amp; Access Control • Department account directory, security roles, and user clearances.
            @elseif (Auth::user()->isTechnical())
                Globaltronics Technical Operations • Stock Requisition Forms (SRF), site installation staging, and warehouse inventory requests.
            @else
                Globaltronics Inventory Control System • Facility monitoring, warehouse stock overview, and logistics telemetry.
            @endif
        </p>
    </div>

    <!-- Quick Action Buttons (Aligned, single-line, no text wrapping) -->
    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 shrink-0">
        <!-- Button 1: Inventory (EOL Units) -->
        <a href="{{ route('admin.inventory.index') }}" 
           class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-500 hover:bg-cyan-600 active:bg-cyan-700 text-slate-950 font-bold text-xs sm:text-sm shadow-sm shadow-cyan-500/25 whitespace-nowrap shrink-0 transition-all hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-cyan-400"
           title="Warehouse Inventory (EOL Units)">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="whitespace-nowrap">Inventory (EOL Units)</span>
        </a>

        <!-- Button 2: Admin Credentials (Restricted to IT Admin) -->
        @if (Auth::user()->canAccessAdminCredentials())
            <a href="{{ route('admin.settings.credentials') }}" 
               class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs sm:text-sm shadow-sm shadow-blue-500/25 whitespace-nowrap shrink-0 transition-all hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-400"
               title="Admin Credentials (User Accounts, Roles & Security)">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span class="whitespace-nowrap">Admin Credentials</span>
            </a>
        @endif

        <!-- Button 3: Portal Login Button -->
        <a href="{{ route('home') }}" target="_blank" 
           class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 border border-slate-300 hover:border-slate-400 font-bold text-xs sm:text-sm shadow-xs whitespace-nowrap shrink-0 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300"
           title="Public Login Portal">
            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            <span class="whitespace-nowrap">Portal Login</span>
        </a>
    </div>
</div>

<!-- LIVE METRICS CARDS -->
@if (Auth::user()->isItAdmin())
    <!-- IT ADMIN: 6 DEPARTMENT ACCOUNTS & ACTIVE USERS METRIC TILES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        
        <!-- Total User Accounts -->
        <a href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Accounts</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 font-mono-code">{{ $totalUsers }}</span>
                <span class="text-xs text-blue-600 font-semibold group-hover:underline">Directory →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Registered users across all departments</p>
        </a>

        <!-- Administrators -->
        <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'role' => 'it-admin']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-purple-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">IT &amp; Admins</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-purple-700 font-mono-code">{{ $adminCount }}</span>
                <span class="text-xs text-purple-600 font-semibold group-hover:underline">Elevated →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">IT Administrator clearance holders</p>
        </a>

        <!-- Warehouse Staff -->
        <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'role' => 'warehouse-staff']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Warehouse Staff</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-700 font-mono-code">{{ $staffCount }}</span>
                <span class="text-xs text-emerald-600 font-semibold group-hover:underline">Floor Ops →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Warehouse floor operators &amp; pickers</p>
        </a>

        <!-- Sales Accounts -->
        <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'role' => 'sales-executive']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-sky-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sales Accounts</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-600 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-sky-700 font-mono-code">{{ $salesCount ?? 0 }}</span>
                <span class="text-xs text-sky-600 font-semibold group-hover:underline">Requisition →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Sales Executives (SSO / SRF authors)</p>
        </a>

        <!-- Roles & Clearances -->
        <a href="{{ route('admin.settings.credentials', ['tab' => 'roles']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-amber-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Security Roles</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-amber-700 font-mono-code">{{ $totalRoles }}</span>
                <span class="text-xs text-amber-600 font-semibold group-hover:underline">Clearances →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Custom clearance levels &amp; RBAC profiles</p>
        </a>

        <!-- Active Users Online -->
        <div onclick="openOnlineUsersModal()" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-400 hover:shadow-md transition-all block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Users Online</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors relative">
                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-600 font-mono-code">{{ $onlineUsersCount ?? 1 }}</span>
                <span class="text-xs text-emerald-600 font-semibold group-hover:underline">View Live →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Active sessions (past 5 min)</p>
        </div>

    </div>
@elseif (Auth::user()->isTechnical())
    <!-- TECHNICAL PERSONNEL: SRF & PROJECT STAGING TILES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total SRF Requisitions -->
        <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-cyan-400 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total SRFs Filed</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-600 group-hover:bg-cyan-500 group-hover:text-slate-950 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-cyan-900 font-mono-code">{{ $userSrfCount }}</span>
                <span class="text-xs text-cyan-700 font-semibold">Requisitions</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Installation project requisitions submitted</p>
        </div>

        <!-- Pending Warehouse Review -->
        <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-amber-400 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Review</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-amber-700 font-mono-code">{{ $userSrfPendingCount }}</span>
                <span class="text-xs text-amber-600 font-semibold animate-pulse">Awaiting Floor Check</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Assigned to Joshua Labios / Felix Tumambing</p>
        </div>

        <!-- Completed & Verified -->
        <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-400 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Verified & Completed</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-700 font-mono-code">{{ $userSrfCompletedCount }}</span>
                <span class="text-xs text-emerald-600 font-semibold">Cleared</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Stock confirmed & allocated to project bay</p>
        </div>

        <!-- Warehouse Available Stock -->
        <a href="{{ route('admin.inventory.index') }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Stock</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-blue-900 font-mono-code">{{ number_format($totalInventoryUnits) }}</span>
                <span class="text-xs text-blue-600 font-semibold group-hover:underline">Browse Items →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Live units available across warehouse bays</p>
        </a>

    </div>
@else
    <!-- WAREHOUSE ADMIN: INVENTORY & OPERATIONS METRIC TILES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Live Inventory Units -->
        <a href="{{ route('admin.inventory.index') }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-cyan-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Inventory Stock</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-600 group-hover:bg-cyan-500 group-hover:text-slate-950 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-cyan-900 font-mono-code">{{ number_format($totalInventoryUnits) }}</span>
                <span class="text-xs text-cyan-700 font-semibold group-hover:underline">Units In Stock →</span>
            </div>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-cyan-500 h-1.5 rounded-full" style="width: 78.4%"></div>
            </div>
            <p class="mt-2 text-[11px] text-slate-500">{{ $totalInventoryModels }} models across storage bays</p>
        </a>

        <!-- Low Stock Items -->
        <a href="{{ route('admin.inventory.index', ['status' => 'low_stock']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-rose-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Low Stock Attention</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-rose-700 font-mono-code">{{ $lowStockCount }}</span>
                <span class="text-xs text-rose-600 font-semibold group-hover:underline">Needs Restock →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Items with 5 or fewer units in warehouse storage</p>
        </a>

        <!-- Warehouse Staff -->
        <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-400 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Floor Operators</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-700 font-mono-code">{{ $staffCount }}</span>
                <span class="text-xs text-emerald-600 font-semibold">Active Staff</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Personnel handling picking, packing, &amp; dispatch</p>
        </div>

        <!-- Defective / Quarantine Units -->
        <a href="{{ route('admin.inventory.index', ['location' => 'DEFECTIVE']) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-amber-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">RMA / Defective Area</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-amber-700 font-mono-code">{{ $defectiveCount }}</span>
                <span class="text-xs text-amber-600 font-semibold group-hover:underline">Quarantine Hold →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Defective modules pending technician repair or scrap</p>
        </a>

    </div>
@endif

<!-- BANNER: ROLE-SPECIFIC COMMAND OVERVIEW -->
@if (Auth::user()->canAccessAdminCredentials())
    <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-50/90 via-indigo-50/50 to-white border border-blue-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                    Credentials & Access Control Hub
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-slate-600">Admin Operations Console</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Admin Operations Console</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                The full credential management suite — user accounts, role definitions, permission clearances, and password rotations — is organized inside <strong>Admin Credentials</strong>.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.settings.credentials') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/25 transition-all hover:scale-[1.02]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span>Open Admin Credentials</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>
@elseif (Auth::user()->isTechnical())
    <div class="p-6 rounded-2xl bg-gradient-to-r from-cyan-50/90 via-sky-50/50 to-white border border-cyan-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-cyan-100 text-cyan-900 border border-cyan-200">
                    Technical Requisitions Command
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-cyan-700">Project Staging Floor</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Stock Requisition Form (SRF)</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Submit project Stock Requisition Forms (SRF) directly to Warehouse Admin (Joshua Labios) and Staff (Felix Tumambing). Track real-time stock availability, reservation staging, and verification clearance.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button 
                type="button" 
                onclick="openInstallationSrfModal()" 
                class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs sm:text-sm shadow-md shadow-cyan-500/25 transition-all hover:scale-[1.02]"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Launch Stock Requisition Form (SRF)</span>
            </button>
        </div>
    </div>
@else
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-50/90 via-teal-50/50 to-white border border-emerald-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Warehouse Operations Hub
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-emerald-700">Facility Floor Active</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Facility Operations Command</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Globaltronics Warehouse Hub is fully operational. Monitoring live storage occupancy, pallet dispatch queues, inventory levels, and automated barcode scanning.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="px-4 py-2.5 rounded-xl bg-white border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Role: Warehouse Administrator</span>
            </div>
        </div>
    </div>
@endif

<!-- ============================================================= -->
<!-- SUBMITTED SRF REQUISITIONS SUMMARY & STATUS OVERVIEW           -->
<!-- Visible to Technical (Ariel Moro) & Warehouse Management       -->
<!-- ============================================================= -->
@if (Auth::user()->isTechnical() || Auth::user()->canViewWarehouseOperations())
<div id="srfRequisitionsSummary" x-data="{ statusFilter: 'all' }" class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-cyan-100 text-cyan-900 border border-cyan-200">
                    FLOWCHART STEP 1 &amp; 2 • REQUISITIONS
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider {{ ($srfPendingCount ?? 0) > 0 ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ $srfPendingCount ?? 0 }} FOR APPROVAL
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200">
                    {{ $srfCompletedCount ?? 0 }} APPROVED
                </span>
                @if (($srfOnHoldCount ?? 0) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-indigo-100 text-indigo-900 border border-indigo-200">
                        {{ $srfOnHoldCount }} ON HOLD
                    </span>
                @endif
                @if (($srfDeclinedCount ?? 0) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-200">
                        {{ $srfDeclinedCount }} DECLINED
                    </span>
                @endif
                <span class="flex items-center gap-1.5 text-xs text-cyan-700 font-bold ml-1">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    SRF PIPELINE LIVE
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span>Stock Requisition Form (SRF) Summary</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl leading-relaxed">
                Summary of Stock Requisition Forms (SRF) submitted to Warehouse Management. Real-time approval workflow: approving automatically deducts inventory quantity and keeps the live ledger in sync.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-auto shrink-0">
            <a 
                href="{{ route('admin.srf.approved') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition-all shadow-xs"
                title="View All Approved & Verified SRFs"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>View Approved SRFs ({{ $srfCompletedCount ?? 0 }}) →</span>
            </a>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button 
            type="button" 
            @click="statusFilter = 'all'" 
            :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
            class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5"
        >
            <span>All</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="statusFilter === 'all' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700'">
                {{ $srfTotalCount ?? ($groupedSrfRequisitions ?? collect())->count() }}
            </span>
        </button>
        <button 
            type="button" 
            @click="statusFilter = 'for_approval'" 
            :class="statusFilter === 'for_approval' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100'"
            class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5"
        >
            <span class="w-2 h-2 rounded-full bg-amber-400" :class="statusFilter === 'for_approval' ? 'bg-white' : 'bg-amber-500'"></span>
            <span>For Approval</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="statusFilter === 'for_approval' ? 'bg-amber-700 text-white' : 'bg-amber-200 text-amber-900'">
                {{ $srfPendingCount ?? 0 }}
            </span>
        </button>
        <button 
            type="button" 
            @click="statusFilter = 'approved'" 
            :class="statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100'"
            class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5"
        >
            <span class="w-2 h-2 rounded-full bg-emerald-400" :class="statusFilter === 'approved' ? 'bg-white' : 'bg-emerald-500'"></span>
            <span>Approved</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="statusFilter === 'approved' ? 'bg-emerald-700 text-white' : 'bg-emerald-200 text-emerald-900'">
                {{ $srfCompletedCount ?? 0 }}
            </span>
        </button>
        <button 
            type="button" 
            @click="statusFilter = 'on_hold'" 
            :class="statusFilter === 'on_hold' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-indigo-50 text-indigo-800 border border-indigo-200 hover:bg-indigo-100'"
            class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5"
        >
            <span class="w-2 h-2 rounded-full bg-indigo-400" :class="statusFilter === 'on_hold' ? 'bg-white' : 'bg-indigo-500'"></span>
            <span>On Hold</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="statusFilter === 'on_hold' ? 'bg-indigo-700 text-white' : 'bg-indigo-200 text-indigo-900'">
                {{ $srfOnHoldCount ?? 0 }}
            </span>
        </button>
        <button 
            type="button" 
            @click="statusFilter = 'declined'" 
            :class="statusFilter === 'declined' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100'"
            class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5"
        >
            <span class="w-2 h-2 rounded-full bg-rose-400" :class="statusFilter === 'declined' ? 'bg-white' : 'bg-rose-500'"></span>
            <span>Declined</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="statusFilter === 'declined' ? 'bg-rose-700 text-white' : 'bg-rose-200 text-rose-900'">
                {{ $srfDeclinedCount ?? 0 }}
            </span>
        </button>
    </div>

    <!-- SRF Summary Table (Combined Multi-Item Rows) -->
    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 uppercase font-mono-code tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4">SRF #</th>
                    <th class="py-3 px-4">SSO #</th>
                    <th class="py-3 px-4">Client</th>
                    <th class="py-3 px-4">PO #</th>
                    <th class="py-3 px-4">Date / Needed</th>
                    <th class="py-3 px-4 min-w-[200px]">ITEM &amp; QTY</th>
                    <th class="py-3 px-4 min-w-[170px]">Remarks</th>
                    <th class="py-3 px-4 min-w-[140px]">Availability</th>
                    <th class="py-3 px-4">Prepared By</th>
                    <th class="py-3 px-4 text-center min-w-[130px]">Status</th>
                    <th class="py-3 px-4 text-center min-w-[150px]">Warehouse Approval</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $grouped = $groupedSrfRequisitions ?? (isset($srfRequisitions) ? $srfRequisitions->groupBy('srf_number') : collect());
                @endphp
                @forelse ($grouped as $srfNumber => $items)
                    @php
                        $first = $items->first();
                        $statusRaw = $first->status;
                        $isForApproval = in_array($statusRaw, ['pending', 'for_approval']);
                        $isApproved = in_array($statusRaw, ['completed', 'verified', 'approved']);
                        $isOnHold = $statusRaw === 'on_hold';
                        $isDeclined = in_array($statusRaw, ['declined', 'rejected']);

                        $groupFilterCategory = 'for_approval';
                        if ($isApproved) {
                            $groupFilterCategory = 'approved';
                        } elseif ($isOnHold) {
                            $groupFilterCategory = 'on_hold';
                        } elseif ($isDeclined) {
                            $groupFilterCategory = 'declined';
                        }
                    @endphp
                    <tr 
                        x-show="statusFilter === 'all' || statusFilter === '{{ $groupFilterCategory }}'" 
                        class="hover:bg-slate-50/80 transition-colors"
                    >
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
                        <td class="py-3.5 px-4 align-top min-w-[200px]">
                            <div class="space-y-2">
                                @foreach ($items as $item)
                                    <div class="flex items-start gap-1.5 {{ !$loop->last ? 'pb-2 border-b border-slate-100' : '' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 shrink-0 mt-1.5"></span>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-800 block truncate" title="{{ $item->inventoryItem?->model ?? 'Custom Hardware' }}">
                                                {{ $item->inventoryItem?->model ?? 'Custom Hardware' }}
                                            </span>
                                            <span class="font-mono-code text-[11px] text-blue-600 font-bold">
                                                {{ $item->quantity }} {{ $item->uom ?? 'PCS' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>

                        <!-- COMBINED REMARKS -->
                        <td class="py-3.5 px-4 align-top max-w-xs min-w-[170px]">
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
                        <td class="py-3.5 px-4 align-top min-w-[140px]">
                            <div class="space-y-2">
                                @foreach ($items as $item)
                                    <div class="{{ !$loop->last ? 'pb-2 border-b border-slate-100' : '' }}">
                                        @if ($item->stock_status === 'available_reserved')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>IN STOCK</span>
                                            </span>
                                            <span class="block text-[10px] text-slate-500 font-mono-code mt-0.5">STAGE-BAY-01</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>PR / MRR HOLD</span>
                                            </span>
                                            <span class="block text-[10px] text-amber-700 font-mono-code mt-0.5">Hold Active</span>
                                        @endif
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

                        <!-- Status (For Approval, Approved, Declined, On Hold) -->
                        <td class="py-3.5 px-4 align-top text-center">
                            @if ($isForApproval)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>FOR APPROVAL</span>
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">Awaiting warehouse review</span>
                            @elseif ($isApproved)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>APPROVED</span>
                                </span>
                                @if ($first->verified_by_name)
                                    <span class="block text-[10px] text-emerald-700 font-mono-code mt-0.5">
                                        By {{ $first->verified_by_name }} • {{ $first->verified_at?->format('M d, h:i A') }}
                                    </span>
                                @endif
                            @elseif ($isOnHold)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                    <svg class="w-3 h-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>ON HOLD</span>
                                </span>
                                @if ($first->verified_by_name)
                                    <span class="block text-[10px] text-indigo-700 font-mono-code mt-0.5">
                                        By {{ $first->verified_by_name }}
                                    </span>
                                @endif
                            @elseif ($isDeclined)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-900 border border-rose-300">
                                    <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>DECLINED</span>
                                </span>
                                @if ($first->verified_by_name)
                                    <span class="block text-[10px] text-rose-700 font-mono-code mt-0.5">
                                        By {{ $first->verified_by_name }}
                                    </span>
                                @endif
                            @endif
                        </td>

                        <!-- Warehouse Action (Approval Decision Controls) -->
                        <td class="py-3.5 px-4 align-top text-center">
                            @if (Auth::user()->canViewWarehouseOperations())
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($isForApproval || $isOnHold)
                                        <!-- Approve Button (Deducts Inventory) -->
                                        <form method="POST" action="{{ route('admin.operations.installation.srf.verify', $first) }}" class="inline" onsubmit="return confirm('Approve SRF #{{ $first->srf_number }}? This will deduct requested hardware from inventory.');">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                            <button 
                                                type="submit" 
                                                class="w-7 h-7 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all hover:scale-105 inline-flex items-center justify-center"
                                                title="Approve SRF & Deduct Inventory"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>

                                        <!-- On Hold Button -->
                                        @if (!$isOnHold)
                                            <form method="POST" action="{{ route('admin.operations.installation.srf.verify', $first) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="decision" value="on_hold">
                                                <button 
                                                    type="submit" 
                                                    class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition-colors inline-flex items-center justify-center"
                                                    title="Put SRF On Hold"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Decline Button (Restores Inventory) -->
                                        <form method="POST" action="{{ route('admin.operations.installation.srf.verify', $first) }}" class="inline" onsubmit="return confirm('Decline SRF #{{ $first->srf_number }}? Any reserved items will be restored to inventory.');">
                                            @csrf
                                            <input type="hidden" name="decision" value="declined">
                                            <button 
                                                type="submit" 
                                                class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors inline-flex items-center justify-center"
                                                title="Decline SRF & Return Reserved Stock"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>

                                        <!-- PDF Form Download -->
                                        <a 
                                            href="{{ route('admin.srf.pdf', urlencode($first->srf_number)) }}" 
                                            target="_blank"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 transition-colors inline-flex items-center justify-center shadow-2xs"
                                            title="Download & Print Official Stock Requisition Form PDF"
                                        >
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>
                                    @elseif ($isApproved)
                                        <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-[11px] inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Staged</span>
                                        </span>
                                        <form method="POST" action="{{ route('admin.operations.installation.srf.verify', $first) }}" class="inline" onsubmit="return confirm('Change SRF #{{ $first->srf_number }} status to ON HOLD?');">
                                            @csrf
                                            <input type="hidden" name="decision" value="on_hold">
                                            <button type="submit" class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 inline-flex items-center justify-center" title="Change status to On Hold">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6" />
                                                </svg>
                                            </button>
                                        </form>
                                        <a 
                                            href="{{ route('admin.srf.pdf', urlencode($first->srf_number)) }}" 
                                            target="_blank"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 transition-colors inline-flex items-center justify-center shadow-2xs"
                                            title="Download & Print Official Stock Requisition Form PDF"
                                        >
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>
                                    @elseif ($isDeclined)
                                        <span class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[11px] inline-flex items-center gap-1">
                                            <span>✕ Restored</span>
                                        </span>
                                        <form method="POST" action="{{ route('admin.operations.installation.srf.verify', $first) }}" class="inline" onsubmit="return confirm('Re-open and Approve SRF #{{ $first->srf_number }}?');">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                            <button type="submit" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 inline-flex items-center justify-center" title="Re-approve Requisition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                        <a 
                                            href="{{ route('admin.srf.pdf', urlencode($first->srf_number)) }}" 
                                            target="_blank"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 transition-colors inline-flex items-center justify-center shadow-2xs"
                                            title="Download & Print Official Stock Requisition Form PDF"
                                        >
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($isForApproval)
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 font-semibold text-[11px] inline-block">
                                            Awaiting Review
                                        </span>
                                    @elseif ($isApproved)
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[11px] inline-block">
                                            ✓ Approved
                                        </span>
                                    @elseif ($isOnHold)
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-[11px] inline-block">
                                            ⏸ On Hold
                                        </span>
                                    @elseif ($isDeclined)
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold text-[11px] inline-block">
                                            ✕ Declined
                                        </span>
                                    @endif

                                    <!-- PDF Form Download -->
                                    <a 
                                        href="{{ route('admin.srf.pdf', urlencode($first->srf_number)) }}" 
                                        target="_blank"
                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 transition-colors inline-flex items-center justify-center shadow-2xs"
                                        title="Download & Print Official Stock Requisition Form PDF"
                                    >
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-sm font-semibold text-slate-600">No Installation SRFs Submitted Yet</span>
                                <p class="text-xs text-slate-400 max-w-sm">Launch an SRF Requisition to initiate site hardware allocation and track availability in real-time.</p>
                                <button type="button" onclick="openInstallationSrfModal()" class="mt-2 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs transition-colors">
                                    + Launch First SRF
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif


<!-- ========================================================================= -->
<!-- MODAL: RECEIVE INBOUND SHIPMENT                                            -->
<!-- ========================================================================= -->
<div id="receiveDeliveryModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeReceiveModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Receive Inbound Delivery</span>
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-mono-code">Step 1.4 & 1.8</span>
                    </h3>
                    <p class="text-xs text-slate-500">Record incoming verified shipment into active inventory</p>
                </div>
                <button type="button" onclick="closeReceiveModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.inventory.store') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500">
                            <option value="CENTRALIZED LED INVENTORY" selected>CENTRALIZED LED INVENTORY</option>
                            <option value="EOL PHILIPS UNITS">EOL PHILIPS UNITS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">DR / PO Number *</label>
                        <input type="text" name="po_number" required placeholder="e.g. PO-2026-0928" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Manufacturer *</label>
                        <select name="manufacturer" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500">
                            <option value="UNILUMIN">UNILUMIN</option>
                            <option value="FABULUX">FABULUX</option>
                            <option value="LKGT - INDOOR LED DISPLAY">LKGT - INDOOR LED DISPLAY</option>
                            <option value="DAHUA TECH">DAHUA TECH</option>
                            <option value="ABSEN">ABSEN</option>
                            <option value="UNIVIEW">UNIVIEW</option>
                            <option value="LIGHT KING">LIGHT KING</option>
                            <option value="LEDTOP">LEDTOP</option>
                            <option value="LEYARD">LEYARD</option>
                            <option value="SAMSUNG">SAMSUNG</option>
                            <option value="TRT">TRT</option>
                            <option value="LINSO">LINSO</option>
                            <option value="GLOBALTRONICS">GLOBALTRONICS</option>
                            <option value="PHILIPS">PHILIPS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date Received *</label>
                        <input type="date" name="check_in_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Model / Pixel Pitch *</label>
                    <input type="text" name="model" required placeholder="e.g. UMINI P1.2 COB or 55BDL4050D" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Item Description *</label>
                    <input type="text" name="item_description" required placeholder="e.g. LED DISPLAY (600 X 337.5MM) - INDOOR" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Location *</label>
                        <select name="location" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold">
                            <option value="MARIKINA" selected>MARIKINA</option>
                            <option value="GLOBALTRONICS">GLOBALTRONICS</option>
                            <option value="A JUAN MAIN 1ST FLOOR">A JUAN MAIN 1ST FLOOR</option>
                            <option value="AJUAN - 1ST FLR">AJUAN - 1ST FLR</option>
                            <option value="A JUAN MAIN 3RD FLOOR">A JUAN MAIN 3RD FLOOR</option>
                            <option value="CLARK OFFICE">CLARK OFFICE</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Quantity (pcs) *</label>
                        <input type="number" name="quantity" min="1" value="10" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total SQM</label>
                        <input type="number" step="0.01" name="sqm" placeholder="0.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeReceiveModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm">Save & Release to Storage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DISPATCH ORDER (STAGE 3)                                            -->
<!-- ========================================================================= -->
<div id="dispatchOrderModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeDispatchModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Pick, Pack & Record Dispatch</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs font-mono-code">Step 3.12</span>
                    </h3>
                    <p class="text-xs text-slate-500">Pick allocated item from warehouse rack and deduct dispatched inventory</p>
                </div>
                <button type="button" onclick="closeDispatchModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.dispatch') }}" class="mt-4 space-y-4">
                @csrf
                <div class="item-search-container relative">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Select Item to Dispatch *</label>
                        <span class="text-[10px] text-slate-400 font-mono-code item-search-count">{{ $inventoryItems->count() }} available</span>
                    </div>
                    <div class="relative mb-1.5">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            placeholder="🔍 Type model, brand, bay location to search..." 
                            autocomplete="off"
                            oninput="handleItemSearch(this)"
                            onfocus="showItemSearchResults(this)"
                            class="item-search-input w-full pl-8 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium"
                        >
                        <button 
                            type="button" 
                            onclick="clearItemSearch(this)" 
                            class="item-search-clear absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                            title="Clear search"
                        >✕</button>
                        <div class="item-search-results absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto hidden divide-y divide-slate-100"></div>
                    </div>
                    <select name="inventory_item_id" required onchange="syncSearchFromSelect(this)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Choose Item from Warehouse Inventory --</option>
                        @foreach ($inventoryItems as $inv)
                            <option value="{{ $inv->id }}" data-model="{{ $inv->model }}" data-mfr="{{ $inv->manufacturer }}" data-location="{{ $inv->location }}" data-qty="{{ $inv->quantity }}" data-search="{{ strtolower($inv->model . ' ' . $inv->manufacturer . ' ' . $inv->location . ' ' . $inv->category) }}">
                                {{ $inv->model }} ({{ $inv->manufacturer }}) • {{ $inv->location }} [{{ $inv->quantity }} pcs available]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dispatch Quantity *</label>
                        <input type="number" name="quantity" min="1" value="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Online Order / PO # *</label>
                        <input type="text" name="order_number" required placeholder="e.g. ORD-2026-8812" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Customer / Recipient Destination *</label>
                        <input type="text" name="recipient" required placeholder="e.g. Clark Installation Project" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Plate / Carrier</label>
                        <input type="text" name="vehicle_plate" placeholder="e.g. NBT-4921 (Global Fleet 4)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dispatch Notes / Gate Pass Info</label>
                    <textarea name="notes" rows="2" placeholder="Picked and packed securely, double checked serials and driver gate pass." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeDispatchModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm">Confirm Pick & Record Dispatch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: CYCLE COUNT AUDIT (STAGE 2)                                         -->
<!-- ========================================================================= -->
<div id="cycleCountModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeCycleCountModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Physical Cycle Count Audit</span>
                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-mono-code">Step 2.2 & 2.4</span>
                    </h3>
                    <p class="text-xs text-slate-500">Record verified physical stock and reconcile system variance</p>
                </div>
                <button type="button" onclick="closeCycleCountModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.cycle-count') }}" class="mt-4 space-y-4">
                @csrf
                <div class="item-search-container relative">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Select Item to Audit *</label>
                        <span class="text-[10px] text-slate-400 font-mono-code item-search-count">{{ $inventoryItems->count() }} items</span>
                    </div>
                    <div class="relative mb-1.5">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            placeholder="🔍 Type model, brand, bay location to search..." 
                            autocomplete="off"
                            oninput="handleItemSearch(this)"
                            onfocus="showItemSearchResults(this)"
                            class="item-search-input w-full pl-8 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 font-medium"
                        >
                        <button 
                            type="button" 
                            onclick="clearItemSearch(this)" 
                            class="item-search-clear absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                            title="Clear search"
                        >✕</button>
                        <div class="item-search-results absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto hidden divide-y divide-slate-100"></div>
                    </div>
                    <select name="inventory_item_id" required onchange="syncSearchFromSelect(this)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Choose Item for Cycle Count --</option>
                        @foreach ($inventoryItems as $inv)
                            <option value="{{ $inv->id }}" data-model="{{ $inv->model }}" data-mfr="{{ $inv->manufacturer }}" data-location="{{ $inv->location }}" data-qty="{{ $inv->quantity }}" data-search="{{ strtolower($inv->model . ' ' . $inv->manufacturer . ' ' . $inv->location . ' ' . $inv->category) }}">
                                {{ $inv->model }} • {{ $inv->location }} [Ledger: {{ $inv->quantity }} pcs]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Physically Counted Quantity (pcs) *</label>
                    <input type="number" name="counted_quantity" min="0" required placeholder="Enter exact count on shelf" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    <p class="text-[11px] text-slate-400 mt-1">If this differs from ledger, variance will be documented and system stock updated.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Auditor & Discrepancy Investigation Notes</label>
                    <textarea name="auditor_notes" rows="2" placeholder="e.g. Conducted physical bay audit on Pallet Rack B. Zero discrepancies detected." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCycleCountModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm">Save Verified Count & Reconcile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RETURN / REVERSE LOGISTICS (STAGE 4)                                -->
<!-- ========================================================================= -->
<div id="returnItemModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeReturnModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Log Returned Material / RMA</span>
                        <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 text-xs font-mono-code">Step 4.3 & 4.4</span>
                    </h3>
                    <p class="text-xs text-slate-500">Record incoming returns, diagnose defects, and route to quarantine or restock</p>
                </div>
                <button type="button" onclick="closeReturnModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.return') }}" class="mt-4 space-y-4">
                @csrf
                <div class="item-search-container relative">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Select Item Model Returned *</label>
                        <span class="text-[10px] text-slate-400 font-mono-code item-search-count">{{ $inventoryItems->count() }} items</span>
                    </div>
                    <div class="relative mb-1.5">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            placeholder="🔍 Type model or brand to search..." 
                            autocomplete="off"
                            oninput="handleItemSearch(this)"
                            onfocus="showItemSearchResults(this)"
                            class="item-search-input w-full pl-8 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium"
                        >
                        <button 
                            type="button" 
                            onclick="clearItemSearch(this)" 
                            class="item-search-clear absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                            title="Clear search"
                        >✕</button>
                        <div class="item-search-results absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto hidden divide-y divide-slate-100"></div>
                    </div>
                    <select name="inventory_item_id" required onchange="syncSearchFromSelect(this)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-rose-500">
                        <option value="">-- Choose Item Model --</option>
                        @foreach ($inventoryItems as $inv)
                            <option value="{{ $inv->id }}" data-model="{{ $inv->model }}" data-mfr="{{ $inv->manufacturer }}" data-location="{{ $inv->location }}" data-qty="{{ $inv->quantity }}" data-search="{{ strtolower($inv->model . ' ' . $inv->manufacturer . ' ' . $inv->location . ' ' . $inv->category) }}">
                                {{ $inv->model }} ({{ $inv->manufacturer }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Returned Quantity (pcs) *</label>
                        <input type="number" name="quantity" min="1" value="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Disposition Decision *</label>
                        <select name="disposition" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold">
                            <option value="quarantine_defective" selected>Quarantine to DEFECTIVE hold area (RMA)</option>
                            <option value="restock">Verified Good — Restock to active inventory</option>
                            <option value="dispose">Scrap / Authorized Disposal</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Serial Numbers Tracked</label>
                    <input type="text" name="serial_numbers" placeholder="e.g. SN-99412A, SN-99413B" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Return / Defect Description *</label>
                    <textarea name="reason" rows="2" required placeholder="e.g. Dead LED cluster on bottom right quadrant. Transferred to DEFECTIVE quarantine for supplier RMA." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeReturnModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm">Record Reverse Logistics Disposition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: WAREHOUSE INTELLIGENCE AUDIT REPORT (STAGE 5)                       -->
<!-- ========================================================================= -->
<div id="reportModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeReportModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-indigo-100 text-indigo-800">ISO 9001:2015 Clause 9.1</span>
                        <span class="text-xs text-slate-400">• Quality Intelligence Audit</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Warehouse Operations Quality Audit</h3>
                    <p class="text-xs text-slate-500">Live operational telemetry & inventory performance report</p>
                </div>
                <button type="button" onclick="closeReportModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Live Metrics Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-5">
                <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-center">
                    <span class="text-[10px] font-bold text-blue-700 uppercase font-mono-code block">Active Units</span>
                    <span class="text-xl font-extrabold text-blue-900 font-mono-code">{{ number_format($totalInventoryUnits) }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-200 text-center">
                    <span class="text-[10px] font-bold text-purple-700 uppercase font-mono-code block">Registered Models</span>
                    <span class="text-xl font-extrabold text-purple-900 font-mono-code">{{ $totalInventoryModels }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-center">
                    <span class="text-[10px] font-bold text-amber-700 uppercase font-mono-code block">Low Stock Alerts</span>
                    <span class="text-xl font-extrabold text-amber-900 font-mono-code">{{ $lowStockCount }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-center">
                    <span class="text-[10px] font-bold text-rose-700 uppercase font-mono-code block">Defective / RMA</span>
                    <span class="text-xl font-extrabold text-rose-900 font-mono-code">{{ $defectiveCount }}</span>
                </div>
            </div>

            <!-- Audit Findings Summary -->
            <div class="space-y-3 text-xs text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="font-bold">Inventory Accuracy Index:</span>
                    <span class="font-mono-code font-bold text-emerald-700">99.4% (Within ±0.6% tolerance)</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="font-bold">Dispatch Velocity & Lead Time:</span>
                    <span class="font-mono-code font-bold text-blue-700">Average 45 mins from Order to Gate Pass</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="font-bold">Quarantine Isolation Ratio:</span>
                    <span class="font-mono-code font-bold text-slate-800">{{ $totalInventoryUnits > 0 ? round(($defectiveCount / $totalInventoryUnits) * 100, 2) : 0 }}% of Total Warehouse Volume</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-bold">Compliance Status:</span>
                    <span class="font-mono-code font-bold text-emerald-700">ISO 9001:2015 Fully Certified</span>
                </div>
            </div>

            <div class="pt-5 border-t border-slate-200 flex items-center justify-between">
                <span class="text-[11px] text-slate-400 font-mono-code">Audited: {{ date('F d, Y') }}</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeReportModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Close</button>
                    <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs">Print Official Report</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: INSTALLATION REQUISITION (SSO & SRF)                               -->
<!-- ========================================================================= -->
<div id="installationSrfModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeInstallationSrfModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-5xl xl:max-w-6xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-7 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase bg-cyan-100 text-cyan-900 border border-cyan-200">
                            STEPS 1 &amp; 2 • STOCK REQUISITION FORM (SRF)
                        </span>
                        <span class="text-xs text-slate-400">• Availability Audit</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Stock Requisition Form (SRF)</h3>
                    <p class="text-xs text-slate-500">Initiate formal project stock requisition with bill of materials &amp; document assignators</p>
                </div>
                <button type="button" onclick="closeInstallationSrfModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.installation.srf') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="project_name" id="srf_project_name_hidden" value="">

                <!-- TOP VALUES: Client, PO, Date Needed, Date, SRF # and SSO # (3 COLUMNS) -->
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        <!-- Client -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Client *</label>
                            <input 
                                type="text" 
                                name="client" 
                                required 
                                placeholder="e.g. SM Prime Holdings / Megamall" 
                                oninput="document.getElementById('srf_project_name_hidden').value = this.value"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-cyan-500"
                            >
                        </div>

                        <!-- PO -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">PO (Purchase Order #) *</label>
                            <input 
                                type="text" 
                                name="po_number" 
                                required 
                                placeholder="e.g. PO-2026-8812" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold font-mono-code focus:ring-2 focus:ring-cyan-500"
                            >
                        </div>

                        <!-- Date Needed -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Date Needed *</label>
                            <input 
                                type="date" 
                                name="date_needed" 
                                required 
                                value="{{ date('Y-m-d', strtotime('+3 days')) }}" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold font-mono-code focus:ring-2 focus:ring-cyan-500"
                            >
                        </div>

                        <!-- Date (Filing Date) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Date (Filing Date) *</label>
                            <input 
                                type="date" 
                                name="requisition_date" 
                                required 
                                value="{{ date('Y-m-d') }}" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold font-mono-code focus:ring-2 focus:ring-cyan-500"
                            >
                        </div>

                        <!-- SRF # (Auto Generated) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">SRF # (Auto Generated) *</label>
                                <span class="text-[10px] font-mono-code font-bold text-cyan-700 bg-cyan-100 px-1.5 py-0.5 rounded border border-cyan-200">AUTO</span>
                            </div>
                            <input 
                                type="text" 
                                name="srf_number" 
                                required 
                                value="{{ $autoSrfNumber ?? (date('Y') . ' - ' . str_pad(956 + max((int)($srfRequisitions->count() ?? 0), 0) + 1, 4, '0', STR_PAD_LEFT)) }}" 
                                placeholder="e.g. {{ date('Y') }} - 0957"
                                class="w-full px-3 py-2 bg-white border border-cyan-300 rounded-xl text-xs font-bold font-mono-code text-cyan-950 focus:ring-2 focus:ring-cyan-500"
                            >
                        </div>

                        <!-- SSO # (Auto Generated) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">SSO # (Auto Generated) *</label>
                                <span class="text-[10px] font-mono-code font-bold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded border border-blue-200">AUTO</span>
                            </div>
                            <input 
                                type="text" 
                                name="sso_number" 
                                required 
                                value="{{ $autoSsoNumber ?? ('SSO-' . date('Y') . '-' . str_pad(($srfRequisitions->count() ?? 0) + 101, 4, '0', STR_PAD_LEFT)) }}" 
                                class="w-full px-3 py-2 bg-white border border-blue-300 rounded-xl text-xs font-bold font-mono-code text-blue-950 focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                </div>

                <!-- REQUISITION ITEMS: COMPACT HORIZONTAL ROW (QTY | UOM | ITEM DESCRIPTION | AVAILABLE QTY | REMARKS) -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Requisition Items</span>
                            <span id="srfItemCountBadge" class="text-[10px] font-mono-code font-bold text-cyan-800 bg-cyan-100 px-2 py-0.5 rounded-full border border-cyan-200">1 Item</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Quick Stock Search Bar with Autocomplete Dropdown -->
                            <div class="relative srf-top-search-container">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                                <input 
                                    type="text" 
                                    id="srfTopSearchInput"
                                    placeholder="Search stock..." 
                                    autocomplete="off"
                                    oninput="handleSrfTopSearch(this.value)" 
                                    onfocus="handleSrfTopSearch(this.value)"
                                    class="pl-7 pr-7 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-cyan-500 w-36 sm:w-56 font-medium transition-all"
                                    title="Quick search hardware stock items"
                                >
                                <button 
                                    type="button" 
                                    id="srfTopSearchClear"
                                    onclick="clearSrfTopSearch()" 
                                    class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                                    title="Clear search"
                                >✕</button>
                                
                                <!-- Floating Top Search Results -->
                                <div id="srfTopSearchResults" class="absolute right-0 top-full mt-1.5 w-80 sm:w-96 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 max-h-64 overflow-y-auto hidden divide-y divide-slate-100"></div>
                            </div>

                            <button 
                                type="button" 
                                onclick="addSrfItemRow()" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs shadow-xs hover:scale-[1.02] transition-all"
                                title="Add another hardware item"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Add Item</span>
                            </button>
                        </div>
                    </div>

                    <!-- Column Header Labels -->
                    <div class="hidden sm:flex items-center gap-2.5 px-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider font-mono-code">
                        <div class="w-20 shrink-0 text-center">QTY *</div>
                        <div class="w-28 shrink-0">UOM *</div>
                        <div class="flex-1 min-w-[220px]">ITEM Description *</div>
                        <div class="w-40 shrink-0 text-center text-amber-700 bg-amber-50/80 rounded py-0.5 border border-amber-200/60">
                            Available QTY <span class="text-[9px] text-slate-400 font-normal">(QTY | SQM)</span>
                        </div>
                        <div class="flex-1 min-w-[180px]">Remarks</div>
                        <div class="w-8 shrink-0 text-center"></div>
                    </div>

                    <!-- Items Container -->
                    <div id="srfItemsContainer" class="space-y-2">
                        <!-- Item Row #1: QTY | UOM | ITEM DESCRIPTION | AVAILABLE QTY | REMARKS -->
                        <div class="srf-item-row flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 p-2 rounded-xl bg-slate-50/80 border border-slate-200 hover:border-slate-300 transition-all" data-index="0">
                            <!-- QTY -->
                            <div class="w-full sm:w-20 shrink-0">
                                <input 
                                    type="number" 
                                    name="items[0][quantity]" 
                                    min="1" 
                                    value="10" 
                                    required 
                                    placeholder="10"
                                    oninput="checkSrfRowStockSufficiency(this)"
                                    class="srf-item-qty-input w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold font-mono-code focus:ring-2 focus:ring-cyan-500 text-center"
                                >
                            </div>

                            <!-- UOM (PCS, KG, ETC) -->
                            <div class="w-full sm:w-28 shrink-0">
                                <select 
                                    name="items[0][uom]" 
                                    required 
                                    class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold focus:ring-2 focus:ring-cyan-500"
                                >
                                    <option value="PCS" selected>PCS (Pieces)</option>
                                    <option value="KG">KG (Kilograms)</option>
                                    <option value="SETS">SETS (Kits)</option>
                                    <option value="BOXES">BOXES</option>
                                    <option value="ROLLS">ROLLS</option>
                                    <option value="METERS">METERS</option>
                                    <option value="UNITS">UNITS</option>
                                    <option value="LOT">LOT</option>
                                </select>
                            </div>

                            <!-- ITEM DESCRIPTION: LIVE SEARCHABLE COMBOBOX -->
                            <div class="w-full sm:flex-1 sm:min-w-[220px] relative srf-row-item-container">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        placeholder="🔍 Search or choose item..." 
                                        autocomplete="off"
                                        oninput="handleSrfRowItemSearch(this)"
                                        onfocus="showSrfRowItemDropdown(this)"
                                        class="srf-item-search-input w-full pl-7 pr-7 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 truncate"
                                        required
                                    >
                                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                                    <button 
                                        type="button" 
                                        onclick="clearSrfRowItem(this)" 
                                        class="srf-item-search-clear absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-rose-600 hidden text-xs font-bold"
                                        title="Clear item selection"
                                    >✕</button>
                                </div>

                                <!-- Hidden native select holding inventory_item_id for submission -->
                                <select 
                                    name="items[0][inventory_item_id]" 
                                    class="inventory-item-select srf-row-item-select hidden"
                                    tabindex="-1"
                                >
                                    <option value="">-- Choose Item Description --</option>
                                    @foreach ($inventoryItems as $inv)
                                        <option 
                                            value="{{ $inv->id }}" 
                                            data-model="{{ $inv->model }}" 
                                            data-mfr="{{ $inv->manufacturer ?? '' }}" 
                                            data-location="{{ $inv->location ?? '' }}" 
                                            data-qty="{{ (int) $inv->quantity }}"
                                            data-acu-qty="{{ (int) ($inv->acu_quantity ?? $inv->quantity) }}"
                                            data-sqm="{{ $inv->sqm ? (float) $inv->sqm : 0 }}"
                                            data-search="{{ strtolower($inv->model . ' ' . ($inv->manufacturer ?? '') . ' ' . ($inv->location ?? '') . ' ' . ($inv->category ?? '')) }}">
                                            {{ $inv->model }} • {{ $inv->location }} [{{ $inv->quantity }} pcs]
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Floating Results Dropdown for Row -->
                                <div class="srf-row-results-dropdown absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 max-h-56 overflow-y-auto hidden divide-y divide-slate-100"></div>
                            </div>

                            <!-- AVAILABLE QTY DISPLAY (QTY | SQM) -->
                            <div class="w-full sm:w-40 shrink-0 flex items-center justify-center">
                                <div class="srf-row-avail-badge w-full py-1.5 px-2 rounded-lg bg-slate-100/90 border border-slate-200 text-center transition-all flex items-center justify-center gap-1.5 font-mono-code text-[11px]">
                                    <span class="srf-avail-qty-val font-bold text-slate-600">—</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="srf-avail-sqm-val font-semibold text-slate-500">—</span>
                                </div>
                            </div>

                            <!-- REMARKS -->
                            <div class="w-full sm:flex-1 sm:min-w-[180px]">
                                <input 
                                    type="text" 
                                    name="items[0][remarks]" 
                                    placeholder="Remarks (e.g. staging notes, test...)" 
                                    class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-cyan-500"
                                >
                            </div>

                            <!-- REMOVE BUTTON -->
                            <div class="w-full sm:w-8 shrink-0 text-center flex items-center justify-center">
                                <button 
                                    type="button" 
                                    onclick="removeSrfItemRow(this)" 
                                    class="srf-remove-item-btn w-7 h-7 inline-flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg text-xs transition-colors hidden"
                                    title="Remove item"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Compact + Add Item Button -->
                    <div class="pt-0.5">
                        <button 
                            type="button" 
                            onclick="addSrfItemRow()" 
                            class="w-full py-1.5 px-3 border border-dashed border-cyan-300 hover:border-cyan-500 rounded-lg text-cyan-700 hover:text-cyan-800 bg-cyan-50/40 hover:bg-cyan-50 font-bold text-xs flex items-center justify-center gap-1.5 transition-all"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Add Another Item</span>
                        </button>
                    </div>
                </div>

                <!-- ASSIGNATORS (4 Columns on Desktop with Department Dynamic Auto-fill) -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-1.5 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Assignators</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <label for="srf_department_select" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">Department:</label>
                            <select name="department" id="srf_department_select" onchange="updateSrfDepartmentAssignators(this.value)" class="text-xs font-bold px-2.5 py-1 bg-white border border-cyan-400 rounded-lg shadow-sm text-cyan-950 focus:ring-2 focus:ring-cyan-500 cursor-pointer">
                                <option value="SALES" selected>SALES</option>
                                <option value="PURCHASING">PURCHASING</option>
                                <option value="TECHNICAL">TECHNICAL</option>
                                <option value="WAREHOUSE">WAREHOUSE</option>
                                <option value="LOGISTICS">LOGISTICS</option>
                                <option value="MARKETING">MARKETING</option>
                                <option value="IT">IT</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                        <div>
                            <label id="srf_label_prepared_by" class="block text-[11px] font-bold text-slate-600 mb-0.5">Prepared By (Sales Admin):</label>
                            <input type="text" name="prepared_by" id="srf_input_prepared_by" value="Anne Libo-on" required class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div>
                            <label id="srf_label_noted_by" class="block text-[11px] font-bold text-slate-600 mb-0.5">Noted By (Sales Admin Manager):</label>
                            <input type="text" name="noted_by" id="srf_input_noted_by" value="Bernadette Federez" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div>
                            <label id="srf_label_pre_approved_by" class="block text-[11px] font-bold text-slate-600 mb-0.5">Pre-Approved By (PMO Technical):</label>
                            <input type="text" name="pre_approved_by" id="srf_input_pre_approved_by" value="Teddy Bajeta" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div>
                            <label id="srf_label_approved_by" class="block text-[11px] font-bold text-slate-600 mb-0.5">Approved By (Chief of Services Officer):</label>
                            <input type="text" name="approved_by" id="srf_input_approved_by" value="Macy Guido Lee" required class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                    </div>
                </div>

                <!-- Decision Branch Indicator -->
                <div class="p-2.5 rounded-xl bg-cyan-50/70 border border-cyan-200 text-xs text-cyan-900 leading-relaxed">
                    <strong>Flow Decision:</strong> If available in warehouse stock, units are allocated immediately to project staging. If stock is insufficient, the system automatically activates <strong>Branch NO (PR &amp; MRR)</strong> with Arbie Hipolito &amp; Teddymar Bajeta to reserve incoming goods upon delivery.
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeInstallationSrfModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-sm transition-transform hover:scale-[1.01]">Submit SRF &amp; Check Availability</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: INTER-FACILITY STOCK TRANSFER OUT (STO)                            -->
<!-- ========================================================================= -->
<div id="installationStoModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeInstallationStoModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase bg-blue-100 text-blue-900 border border-blue-200">
                            STEP 5 • INTER-FACILITY STO
                        </span>
                        <span class="text-xs text-slate-400">• Transfer Pipeline</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Stock Transfer Out (STO) Manifest</h3>
                    <p class="text-xs text-slate-500">Document inter-facility or bay transfers before technical QA bench testing</p>
                </div>
                <button type="button" onclick="closeInstallationStoModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.installation.sto') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Project Name *</label>
                        <input type="text" name="project_name" required placeholder="e.g. Clark LED Installation Project" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">STO Number *</label>
                        <input type="text" name="sto_number" required placeholder="e.g. STO-2026-0128" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 item-search-container relative">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Item to Transfer *</label>
                            <span class="text-[10px] text-slate-400 font-mono-code item-search-count">{{ $inventoryItems->count() }} items</span>
                        </div>
                        <div class="relative mb-1.5">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                placeholder="🔍 Type model, brand, bay location to search..." 
                                autocomplete="off"
                                oninput="handleItemSearch(this)"
                                onfocus="showItemSearchResults(this)"
                                class="item-search-input w-full pl-8 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium"
                            >
                            <button 
                                type="button" 
                                onclick="clearItemSearch(this)" 
                                class="item-search-clear absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                                title="Clear search"
                            >✕</button>
                            <div class="item-search-results absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto hidden divide-y divide-slate-100"></div>
                        </div>
                        <select name="inventory_item_id" required onchange="syncSearchFromSelect(this)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Choose Hardware from Stock --</option>
                            @foreach ($inventoryItems as $inv)
                                <option value="{{ $inv->id }}" data-model="{{ $inv->model }}" data-mfr="{{ $inv->manufacturer }}" data-location="{{ $inv->location }}" data-qty="{{ $inv->quantity }}" data-search="{{ strtolower($inv->model . ' ' . $inv->manufacturer . ' ' . $inv->location . ' ' . $inv->category) }}">
                                    {{ $inv->model }} • Location: {{ $inv->location }} ({{ $inv->quantity }} pcs)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Transfer Qty *</label>
                        <input type="number" name="quantity" min="1" value="5" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Origin Source Bay *</label>
                        <input type="text" name="source_bay" required value="MARIKINA MAIN WAREHOUSE" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Destination Testing / Staging Bay *</label>
                        <input type="text" name="destination_bay" required value="TECH QA TESTING BENCH (MARIKINA)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                </div>

                <!-- STO Assignatories -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">STO Assignatories (Sign-Off Matrix)</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Prepared By:</label>
                            <input type="text" name="prepared_by" value="Arbie Hipolito" required class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Checked By:</label>
                            <input type="text" name="checked_by" value="Ryan Lomboy / Marikina Guard" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Released By:</label>
                            <input type="text" name="released_by" value="Ryan Matuguina / Daylyn Olafe" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Approved By:</label>
                            <input type="text" name="approved_by" value="Joshua Labios" required class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeInstallationStoModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm">Confirm STO &amp; Send to QA Bench</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DELIVERY RECEIPT (DR) & OUTBOUND DISPATCH                          -->
<!-- ========================================================================= -->
<div id="installationDrModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeInstallationDrModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase bg-indigo-100 text-indigo-900 border border-indigo-200">
                            STEPS 8 & 9 • DELIVERY RECEIPT (DR)
                        </span>
                        <span class="text-xs text-slate-400">• Outbound Release</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Issue Delivery Receipt &amp; Release Units</h3>
                    <p class="text-xs text-slate-500">Official dispatch documentation, vehicle gate clearance &amp; site handoff</p>
                </div>
                <button type="button" onclick="closeInstallationDrModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.installation.dr') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Project Name *</label>
                        <input type="text" name="project_name" required placeholder="e.g. Clark Installation Project" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Official DR Number *</label>
                        <input type="text" name="dr_number" required placeholder="e.g. DR-2026-4402" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 item-search-container relative">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Item to Dispatch *</label>
                            <span class="text-[10px] text-slate-400 font-mono-code item-search-count">{{ $inventoryItems->count() }} available</span>
                        </div>
                        <div class="relative mb-1.5">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                placeholder="🔍 Type model, brand, bay location to search..." 
                                autocomplete="off"
                                oninput="handleItemSearch(this)"
                                onfocus="showItemSearchResults(this)"
                                class="item-search-input w-full pl-8 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                            >
                            <button 
                                type="button" 
                                onclick="clearItemSearch(this)" 
                                class="item-search-clear absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                                title="Clear search"
                            >✕</button>
                            <div class="item-search-results absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto hidden divide-y divide-slate-100"></div>
                        </div>
                        <select name="inventory_item_id" required onchange="syncSearchFromSelect(this)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose Hardware from Stock --</option>
                            @foreach ($inventoryItems as $inv)
                                <option value="{{ $inv->id }}" data-model="{{ $inv->model }}" data-mfr="{{ $inv->manufacturer }}" data-location="{{ $inv->location }}" data-qty="{{ $inv->quantity }}" data-search="{{ strtolower($inv->model . ' ' . $inv->manufacturer . ' ' . $inv->location . ' ' . $inv->category) }}">
                                    {{ $inv->model }} ({{ $inv->manufacturer }}) • {{ $inv->location }} [{{ $inv->quantity }} pcs available]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dispatch Qty *</label>
                        <input type="number" name="quantity" min="1" value="5" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Installation Site / Recipient *</label>
                        <input type="text" name="recipient_destination" required placeholder="e.g. SM Mall of Asia Activity Center" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Plate / Logistics Carrier</label>
                        <input type="text" name="vehicle_plate" placeholder="e.g. NBT-4921 (Global Fleet Van 3)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                </div>

                <!-- DR Assignatories -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">DR Assignatories (Official Endorsement)</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Prepared By:</label>
                            <input type="text" name="prepared_by" value="Arbie Hipolito / Francis Perez" required class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Checked By:</label>
                            <input type="text" name="checked_by" value="Jerico Rivera / Josephine Lim" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Released By:</label>
                            <input type="text" name="released_by" value="Felix Tumambing / Charles Quiachon" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Approved By:</label>
                            <input type="text" name="approved_by" value="Joshua Labios" required class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeInstallationDrModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">Authorize DR &amp; Release to Site</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RETURN SLIP & REVERSE LOGISTICS / EOL TRIAGE                       -->
<!-- ========================================================================= -->
<div id="installationReturnModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeInstallationReturnModal()"></div>
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 z-10 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase bg-rose-100 text-rose-900 border border-rose-200">
                            STEP 11 • RETURN SLIP &amp; EOL TRIAGE
                        </span>
                        <span class="text-xs text-slate-400">• Reverse Logistics</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Process Project Return Slip &amp; EOL</h3>
                    <p class="text-xs text-slate-500">Handle leftover returns, technician diagnostics, and write-off to EOL items</p>
                </div>
                <button type="button" onclick="closeInstallationReturnModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.operations.installation.return') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Project Name *</label>
                        <input type="text" name="project_name" required placeholder="e.g. SM Mall of Asia Installation" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Return Slip Number *</label>
                        <input type="text" name="return_slip_number" required placeholder="e.g. RS-2026-0091" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 item-search-container relative">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Hardware Model *</label>
                            <span class="text-[10px] text-slate-400 font-mono-code item-search-count">{{ $inventoryItems->count() }} items</span>
                        </div>
                        <div class="relative mb-1.5">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                placeholder="🔍 Type model, category, brand to search..." 
                                autocomplete="off"
                                oninput="handleItemSearch(this)"
                                onfocus="showItemSearchResults(this)"
                                class="item-search-input w-full pl-8 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium"
                            >
                            <button 
                                type="button" 
                                onclick="clearItemSearch(this)" 
                                class="item-search-clear absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 hidden text-xs font-bold"
                                title="Clear search"
                            >✕</button>
                            <div class="item-search-results absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto hidden divide-y divide-slate-100"></div>
                        </div>
                        <select name="inventory_item_id" required onchange="syncSearchFromSelect(this)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                            <option value="">-- Choose Item Model --</option>
                            @foreach ($inventoryItems as $inv)
                                <option value="{{ $inv->id }}" data-model="{{ $inv->model }}" data-mfr="{{ $inv->manufacturer }}" data-location="{{ $inv->location }}" data-qty="{{ $inv->quantity }}" data-search="{{ strtolower($inv->model . ' ' . $inv->manufacturer . ' ' . $inv->location . ' ' . $inv->category) }}">
                                    {{ $inv->model }} • Category: {{ $inv->category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" min="1" value="2" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono-code">
                    </div>
                </div>

                <!-- Decision Branch Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Flowchart Decision &amp; Triage Outcome *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-100 cursor-pointer">
                            <input type="radio" name="disposition_flow" value="no_returns_consumed" class="mt-0.5 text-slate-700 focus:ring-slate-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">BRANCH NO: Deduct to Inventory</span>
                                <span class="text-[10px] text-slate-500">All materials consumed onsite, no returns.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100 cursor-pointer">
                            <input type="radio" name="disposition_flow" value="restock_good" checked class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">FOR REPAIR? NO: Adjust Inventory</span>
                                <span class="text-[10px] text-emerald-700">Good pullout units restocked to active inventory.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-amber-200 bg-amber-50/50 hover:bg-amber-100 cursor-pointer">
                            <input type="radio" name="disposition_flow" value="technician_repair" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="text-xs font-bold text-amber-900 block">FOR REPAIR? YES: Transfer to Technician</span>
                                <span class="text-[10px] text-amber-700">Defective units routed to diagnostic bench.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-100 cursor-pointer">
                            <input type="radio" name="disposition_flow" value="eol_scrap" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">REPAIRED? NO: Add to EOL Items</span>
                                <span class="text-[10px] text-rose-700">Unrepairable scrap added to EOL Units inventory.</span>
                            </div>
                        </label>

                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Serial Numbers</label>
                        <input type="text" name="serial_numbers" placeholder="e.g. SN-LED-0091, SN-LED-0092" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono-code">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Inspection Findings / Reason</label>
                        <input type="text" name="findings" placeholder="e.g. Leftover spares / burned receiver board" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                </div>

                <!-- Return Slip Assignatories -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Return Slip Assignatories</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Returned By:</label>
                            <input type="text" name="returned_by" value="Technical Staff" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Received By:</label>
                            <input type="text" name="received_by" value="Francis Perez / Henry Paja" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Approved By:</label>
                            <input type="text" name="approved_by" value="Jerico Rivera / Josephine Lim" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeInstallationReturnModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm">Process Return Slip &amp; Update Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC: TAB SWITCHING, PERSISTENT CHECKLISTS & MODALS           -->
<!-- ========================================================================= -->
<script>
    const STAGE_CONFIGS = {
        'stage1': { title: '1. Receiving of Delivery Materials', count: 8, prefix: 's1_' },
        'stage2': { title: '2. Inventory Management', count: 4, prefix: 's2_' },
        'stage3': { title: '3. Post Order / Pre Order Online', count: 12, prefix: 's3_' },
        'stage4': { title: '4. Returns / Reverse Logistics', count: 4, prefix: 's4_' },
        'stage5': { title: '5. Reporting & Quality Improvement', count: 4, prefix: 's5_' },
        'installationProject': { title: 'Warehouse Process Flow — Installation Project', count: 10, prefix: 'ip_' },
        'master': { title: 'Master Lifecycle Pipeline (All Stages)', count: 32, prefix: 'all' }
    };

    let currentSopStage = 'stage1';

    function switchSopTab(stageId) {
        currentSopStage = stageId;

        // Hide all stage panels
        document.querySelectorAll('.sop-stage-panel').forEach(p => p.classList.add('hidden'));
        const targetPanel = document.getElementById('sopStage-' + stageId);
        if (targetPanel) {
            targetPanel.classList.remove('hidden');
        }

        // Update button states
        document.querySelectorAll('.sop-tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'bg-amber-600', 'bg-emerald-600', 'bg-rose-600', 'bg-indigo-600', 'bg-cyan-700', 'text-white', 'shadow-sm');
            btn.classList.add('bg-slate-100', 'text-slate-700');
        });

        const activeBtn = document.getElementById('tabBtn-' + stageId);
        if (activeBtn) {
            activeBtn.classList.remove('bg-slate-100', 'text-slate-700');
            const colorClass = stageId === 'stage2' ? 'bg-amber-600' :
                               (stageId === 'stage3' ? 'bg-emerald-600' :
                               (stageId === 'stage4' ? 'bg-rose-600' :
                               (stageId === 'stage5' ? 'bg-indigo-600' : (stageId === 'installationProject' ? 'bg-cyan-700' : 'bg-blue-600'))));
            activeBtn.classList.add(colorClass, 'text-white', 'shadow-sm');
        }

        updateStageProgressDisplay();
    }

    function toggleSopItem(key) {
        let state = JSON.parse(localStorage.getItem('wms_sop_state') || '{}');
        state[key] = !state[key];
        localStorage.setItem('wms_sop_state', JSON.stringify(state));
        applySopCheckboxUI(key, state[key]);
        updateStageProgressDisplay();
    }

    function applySopCheckboxUI(key, isChecked) {
        const box = document.getElementById('chk-' + key);
        if (!box) return;
        const icon = box.querySelector('.check-icon');

        if (isChecked) {
            box.classList.remove('border-slate-300', 'border-amber-400', 'border-emerald-500');
            box.classList.add('bg-emerald-500', 'border-emerald-500');
            if (icon) icon.classList.remove('hidden');
        } else {
            box.classList.remove('bg-emerald-500', 'border-emerald-500');
            box.classList.add('border-slate-300');
            if (icon) icon.classList.add('hidden');
        }
    }

    function updateStageProgressDisplay() {
        const config = STAGE_CONFIGS[currentSopStage];
        if (!config) return;

        const state = JSON.parse(localStorage.getItem('wms_sop_state') || '{}');
        let completed = 0;

        if (currentSopStage === 'master') {
            completed = Object.values(state).filter(Boolean).length;
        } else {
            for (let i = 1; i <= config.count; i++) {
                if (state[config.prefix + i]) {
                    completed++;
                }
            }
        }

        const pct = config.count > 0 ? Math.round((completed / config.count) * 100) : 0;
        const titleEl = document.getElementById('currentStageTitle');
        const textEl = document.getElementById('currentStageProgressText');
        const barEl = document.getElementById('currentStageProgressBar');

        if (titleEl) titleEl.innerText = config.title;
        if (textEl) textEl.innerText = `${completed} of ${config.count} steps completed (${pct}% compliance)`;
        if (barEl) {
            barEl.style.width = pct + '%';
            barEl.className = 'h-2 rounded-full transition-all duration-300 ' + 
                (pct === 100 ? 'bg-emerald-500' : (pct >= 50 ? 'bg-blue-600' : 'bg-amber-500'));
        }
    }

    function markAllCurrentStage() {
        const config = STAGE_CONFIGS[currentSopStage];
        if (!config || currentSopStage === 'master') return;

        let state = JSON.parse(localStorage.getItem('wms_sop_state') || '{}');
        for (let i = 1; i <= config.count; i++) {
            const key = config.prefix + i;
            state[key] = true;
            applySopCheckboxUI(key, true);
        }
        localStorage.setItem('wms_sop_state', JSON.stringify(state));
        updateStageProgressDisplay();
    }

    function resetCurrentStageChecklist() {
        const config = STAGE_CONFIGS[currentSopStage];
        if (!config) return;

        let state = JSON.parse(localStorage.getItem('wms_sop_state') || '{}');
        if (currentSopStage === 'master') {
            state = {};
            document.querySelectorAll('.sop-check-box').forEach(b => {
                b.classList.remove('bg-emerald-500', 'border-emerald-500');
                b.classList.add('border-slate-300');
                const ic = b.querySelector('.check-icon');
                if (ic) ic.classList.add('hidden');
            });
        } else {
            for (let i = 1; i <= config.count; i++) {
                const key = config.prefix + i;
                delete state[key];
                applySopCheckboxUI(key, false);
            }
        }
        localStorage.setItem('wms_sop_state', JSON.stringify(state));
        updateStageProgressDisplay();
    }

    // Modal helpers
    function openReceiveModal() { document.getElementById('receiveDeliveryModal')?.classList.remove('hidden'); }
    function closeReceiveModal() { document.getElementById('receiveDeliveryModal')?.classList.add('hidden'); }
    function openDispatchModal() { document.getElementById('dispatchOrderModal')?.classList.remove('hidden'); }
    function closeDispatchModal() { document.getElementById('dispatchOrderModal')?.classList.add('hidden'); }
    function openCycleCountModal() { document.getElementById('cycleCountModal')?.classList.remove('hidden'); }
    function closeCycleCountModal() { document.getElementById('cycleCountModal')?.classList.add('hidden'); }
    function openReturnModal() { document.getElementById('returnItemModal')?.classList.remove('hidden'); }
    function closeReturnModal() { document.getElementById('returnItemModal')?.classList.add('hidden'); }
    function openReportModal() { document.getElementById('reportModal')?.classList.remove('hidden'); }
    function closeReportModal() { document.getElementById('reportModal')?.classList.add('hidden'); }

    // Installation Project Modal Helpers
    function openInstallationSrfModal() { document.getElementById('installationSrfModal')?.classList.remove('hidden'); }
    function closeInstallationSrfModal() { document.getElementById('installationSrfModal')?.classList.add('hidden'); }
    function openInstallationStoModal() { document.getElementById('installationStoModal')?.classList.remove('hidden'); }
    function closeInstallationStoModal() { document.getElementById('installationStoModal')?.classList.add('hidden'); }
    function openInstallationDrModal() { document.getElementById('installationDrModal')?.classList.remove('hidden'); }
    function closeInstallationDrModal() { document.getElementById('installationDrModal')?.classList.add('hidden'); }
    function openInstallationReturnModal() { document.getElementById('installationReturnModal')?.classList.remove('hidden'); }
    function closeInstallationReturnModal() { document.getElementById('installationReturnModal')?.classList.add('hidden'); }

    // ==========================================
    // SRF DEPARTMENT ASSIGNATORS DYNAMIC MATRIX
    // ==========================================
    const srfDepartmentProfiles = {
        'SALES': {
            prepared_by_label: 'Prepared By (Sales Admin):',
            prepared_by_val: 'Anne Libo-on',
            noted_by_label: 'Noted By (Sales Admin Manager):',
            noted_by_val: 'Bernadette Federez',
            pre_approved_by_label: 'Pre-Approved By (PMO Technical):',
            pre_approved_by_val: 'Teddy Bajeta',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        },
        'PURCHASING': {
            prepared_by_label: 'Prepared By (Purchasing):',
            prepared_by_val: 'Darriane Imperial',
            noted_by_label: 'Noted By:',
            noted_by_val: '',
            pre_approved_by_label: 'Pre-Approved By (PMO Technical Officer):',
            pre_approved_by_val: 'Teddy Bajeta',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        },
        'TECHNICAL': {
            prepared_by_label: 'Prepared By (Project Team Lead):',
            prepared_by_val: 'Ariel Moro',
            noted_by_label: 'Noted By (Warehouse / Project):',
            noted_by_val: 'Joshua Labios / Felix Tumambing',
            pre_approved_by_label: 'Pre-Approved By (PMO Technical):',
            pre_approved_by_val: 'Teddymar Bajeta',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        },
        'WAREHOUSE': {
            prepared_by_label: 'Prepared By (Warehouse):',
            prepared_by_val: 'Warehouse Team',
            noted_by_label: 'Noted By:',
            noted_by_val: '',
            pre_approved_by_label: 'Pre-Approved By:',
            pre_approved_by_val: '',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        },
        'LOGISTICS': {
            prepared_by_label: 'Prepared By (Logistics):',
            prepared_by_val: 'Logistics Team',
            noted_by_label: 'Noted By:',
            noted_by_val: '',
            pre_approved_by_label: 'Pre-Approved By (PMO Technical):',
            pre_approved_by_val: 'Teddy Bajeta',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        },
        'MARKETING': {
            prepared_by_label: 'Prepared By (Marketing):',
            prepared_by_val: 'Marketing Team',
            noted_by_label: 'Noted By:',
            noted_by_val: '',
            pre_approved_by_label: 'Pre-Approved By (PMO Technical):',
            pre_approved_by_val: 'Teddy Bajeta',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        },
        'IT': {
            prepared_by_label: 'Prepared By (IT Specialist):',
            prepared_by_val: 'Stephanie Refe',
            noted_by_label: 'Noted By (Senior IT R&D):',
            noted_by_val: 'Paz Liquigan',
            pre_approved_by_label: 'Pre-Approved By (PMO Technical Officer):',
            pre_approved_by_val: 'Teddy Bajeta',
            approved_by_label: 'Approved By (Chief of Services Officer):',
            approved_by_val: 'Macy Guido Lee'
        }
    };

    function updateSrfDepartmentAssignators(dept) {
        const profile = srfDepartmentProfiles[dept];
        if (!profile) return;

        const prepLabel = document.getElementById('srf_label_prepared_by');
        const prepInput = document.getElementById('srf_input_prepared_by');
        const notedLabel = document.getElementById('srf_label_noted_by');
        const notedInput = document.getElementById('srf_input_noted_by');
        const preAppLabel = document.getElementById('srf_label_pre_approved_by');
        const preAppInput = document.getElementById('srf_input_pre_approved_by');
        const appLabel = document.getElementById('srf_label_approved_by');
        const appInput = document.getElementById('srf_input_approved_by');

        if (prepLabel) prepLabel.textContent = profile.prepared_by_label;
        if (prepInput) prepInput.value = profile.prepared_by_val;
        if (notedLabel) notedLabel.textContent = profile.noted_by_label;
        if (notedInput) notedInput.value = profile.noted_by_val;
        if (preAppLabel) preAppLabel.textContent = profile.pre_approved_by_label;
        if (preAppInput) preAppInput.value = profile.pre_approved_by_val;
        if (appLabel) appLabel.textContent = profile.approved_by_label;
        if (appInput) appInput.value = profile.approved_by_val;
    }

    // Init checkboxes from localStorage on load
    document.addEventListener('DOMContentLoaded', function() {
        const state = JSON.parse(localStorage.getItem('wms_sop_state') || '{}');
        Object.keys(state).forEach(key => {
            if (state[key]) {
                applySopCheckboxUI(key, true);
            }
        });
        updateStageProgressDisplay();
    });

    // ==========================================
    // ITEM SEARCH FOR MODALS (Every Item Search)
    // ==========================================
    function handleItemSearch(input) {
        const container = input.closest('.item-search-container');
        if (!container) return;

        const query = input.value.trim().toLowerCase();
        const resultsBox = container.querySelector('.item-search-results');
        const clearBtn = container.querySelector('.item-search-clear');
        const select = container.querySelector('select.inventory-item-select, select[name*="inventory_item_id"], select');
        const countBadge = container.querySelector('.item-search-count');

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', query.length === 0);
        }

        if (!select) return;
        const options = Array.from(select.querySelectorAll('option')).filter(opt => opt.value !== '');

        let matchCount = 0;
        let resultsHtml = '';

        options.forEach(opt => {
            const searchData = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
            const isMatch = query.length === 0 || searchData.includes(query);

            opt.hidden = !isMatch;
            if (isMatch) {
                matchCount++;
                const model = opt.getAttribute('data-model') || opt.textContent.trim();
                const mfr = opt.getAttribute('data-mfr') || '';
                const loc = opt.getAttribute('data-location') || '';
                const qty = opt.getAttribute('data-qty') || '';

                resultsHtml += `
                    <div class="p-2.5 hover:bg-slate-50 cursor-pointer flex items-center justify-between text-xs transition-colors group"
                         onclick="selectItemFromSearch(this, '${opt.value}')"
                         data-val="${opt.value}">
                        <div>
                            <div class="font-bold text-slate-800 group-hover:text-blue-600">${model}</div>
                            <div class="text-[11px] text-slate-500 font-mono-code flex items-center gap-1.5 mt-0.5">
                                ${mfr ? '<span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">' + mfr + '</span>' : ''}
                                ${loc ? '<span>Location: <strong>' + loc + '</strong></span>' : ''}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono-code ${parseInt(qty) > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                ${qty ? qty + ' pcs' : 'Available'}
                            </span>
                        </div>
                    </div>
                `;
            }
        });

        if (countBadge) {
            countBadge.textContent = query.length > 0 ? `${matchCount} found` : `${options.length} in stock`;
        }

        if (resultsBox) {
            if (query.length > 0) {
                if (matchCount > 0) {
                    resultsBox.innerHTML = resultsHtml;
                    resultsBox.classList.remove('hidden');
                } else {
                    resultsBox.innerHTML = `<div class="p-3 text-center text-xs text-slate-400 italic">No matching hardware items found in stock.</div>`;
                    resultsBox.classList.remove('hidden');
                }
            } else {
                resultsBox.classList.add('hidden');
            }
        }
    }

    function showItemSearchResults(input) {
        if (input.value.trim().length > 0) {
            handleItemSearch(input);
        }
    }

    function selectItemFromSearch(el, val) {
        const container = el.closest('.item-search-container');
        if (!container) return;

        const select = container.querySelector('select.inventory-item-select, select[name*="inventory_item_id"], select');
        const input = container.querySelector('.item-search-input');
        const resultsBox = container.querySelector('.item-search-results');
        const clearBtn = container.querySelector('.item-search-clear');

        if (select) {
            select.value = val;
            select.dispatchEvent(new Event('change'));
        }

        const selectedOpt = select?.querySelector(`option[value="${val}"]`);
        if (input && selectedOpt) {
            input.value = selectedOpt.getAttribute('data-model') || selectedOpt.textContent.trim();
        }

        if (resultsBox) resultsBox.classList.add('hidden');
        if (clearBtn) clearBtn.classList.remove('hidden');
    }

    function syncSearchFromSelect(select) {
        const container = select.closest('.item-search-container');
        if (!container) return;

        const input = container.querySelector('.item-search-input');
        const clearBtn = container.querySelector('.item-search-clear');
        const selectedOpt = select.selectedOptions[0];

        if (input && selectedOpt && selectedOpt.value !== '') {
            input.value = selectedOpt.getAttribute('data-model') || selectedOpt.textContent.trim();
            if (clearBtn) clearBtn.classList.remove('hidden');
        } else if (input && (!selectedOpt || selectedOpt.value === '')) {
            input.value = '';
            if (clearBtn) clearBtn.classList.add('hidden');
        }
    }

    function clearItemSearch(btn) {
        const container = btn.closest('.item-search-container');
        if (!container) return;

        const input = container.querySelector('.item-search-input');
        const select = container.querySelector('select.inventory-item-select, select[name*="inventory_item_id"], select');
        const resultsBox = container.querySelector('.item-search-results');
        const countBadge = container.querySelector('.item-search-count');

        if (input) input.value = '';
        if (resultsBox) resultsBox.classList.add('hidden');
        btn.classList.add('hidden');

        if (select) {
            const options = Array.from(select.querySelectorAll('option'));
            options.forEach(opt => { opt.hidden = false; });
            select.value = '';
            select.dispatchEvent(new Event('change'));
            if (countBadge) {
                const total = options.filter(opt => opt.value !== '').length;
                countBadge.textContent = `${total} in stock`;
            }
        }
    }

    // ==========================================
    // MULTI-ITEM REQUISITION HANDLER (COMPACT ROWS)
    // ==========================================
    let srfItemIndex = 1;

    // =========================================================================
    // SRF REQUISITION STOCK SEARCH & LIVE AVAILABLE QUANTITY CONTROLLER
    // =========================================================================
    @php
        $srfInventoryStockJson = $inventoryItems->map(function($i) {
            $divisor = ($i->acu_quantity ?: $i->quantity) ?: 1;
            $perPanelSqm = ($i->sqm && $divisor > 0) ? round($i->sqm / $divisor, 2) : 0;
            return [
                'id' => (string) $i->id,
                'model' => (string) $i->model,
                'mfr' => (string) ($i->manufacturer ?? ''),
                'location' => (string) ($i->location ?? 'Warehouse'),
                'qty' => (int) $i->quantity,
                'acu_qty' => (int) ($i->acu_quantity ?? $i->quantity),
                'sqm' => $i->sqm ? (float) $i->sqm : 0,
                'per_panel_sqm' => $perPanelSqm,
                'search' => strtolower($i->model . ' ' . ($i->manufacturer ?? '') . ' ' . ($i->location ?? '') . ' ' . ($i->category ?? '')),
            ];
        })->values();
    @endphp
    const srfInventoryStock = {!! json_encode($srfInventoryStockJson) !!};

    let srfItemIndex = 1;

    // TOP SEARCH: Quick Stock Search Bar in Header
    function handleSrfTopSearch(query) {
        const q = (query || '').trim().toLowerCase();
        const resultsBox = document.getElementById('srfTopSearchResults');
        const clearBtn = document.getElementById('srfTopSearchClear');
        if (!resultsBox) return;

        if (clearBtn) clearBtn.classList.toggle('hidden', q.length === 0);

        if (q.length === 0) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        const matches = srfInventoryStock.filter(item => item.search.includes(q));

        if (matches.length === 0) {
            resultsBox.innerHTML = `<div class="p-3 text-center text-xs text-slate-400 italic">No matching items found in stock for "${escapeHtml(query)}"</div>`;
        } else {
            resultsBox.innerHTML = `
                <div class="px-3 py-1.5 bg-slate-50 text-[11px] font-bold text-slate-500 border-b border-slate-100 flex items-center justify-between">
                    <span>${matches.length} matching item${matches.length === 1 ? '' : 's'}</span>
                    <span class="text-[10px] text-cyan-600 font-semibold">Click to assign or add</span>
                </div>
                ${matches.slice(0, 30).map(item => `
                    <div class="p-2.5 hover:bg-cyan-50/70 cursor-pointer flex items-center justify-between text-xs transition-colors group"
                         onclick="selectSrfItemFromTopSearch('${item.id}')">
                        <div class="min-w-0 pr-2">
                            <div class="font-bold text-slate-800 group-hover:text-cyan-700 truncate">${escapeHtml(item.model)}</div>
                            <div class="text-[11px] text-slate-500 font-mono-code flex flex-wrap items-center gap-1.5 mt-0.5">
                                ${item.mfr ? '<span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-semibold">' + escapeHtml(item.mfr) + '</span>' : ''}
                                <span>Bay: <strong class="text-slate-700">${escapeHtml(item.location)}</strong></span>
                                ${item.sqm > 0 ? '<span class="text-slate-400">• ' + item.sqm + ' sqm</span>' : ''}
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono-code ${item.qty > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                    ${item.qty} pcs
                                </span>
                            </div>
                            <span class="px-2 py-1 rounded bg-cyan-600 text-white font-bold text-[10px] group-hover:bg-cyan-500 transition-colors shadow-xs">+ Add</span>
                        </div>
                    </div>
                `).join('')}
            `;
        }
        resultsBox.classList.remove('hidden');
    }

    function clearSrfTopSearch() {
        const input = document.getElementById('srfTopSearchInput');
        const clearBtn = document.getElementById('srfTopSearchClear');
        const resultsBox = document.getElementById('srfTopSearchResults');
        if (input) input.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        if (resultsBox) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
        }
    }

    function selectSrfItemFromTopSearch(itemId) {
        const item = srfInventoryStock.find(i => String(i.id) === String(itemId));
        if (!item) return;

        const container = document.getElementById('srfItemsContainer');
        if (!container) return;

        // Find first row without an item selected
        const rows = container.querySelectorAll('.srf-item-row');
        let targetRow = null;

        for (const row of rows) {
            const sel = row.querySelector('.srf-row-item-select');
            if (sel && !sel.value) {
                targetRow = row;
                break;
            }
        }

        // If no empty row exists, add a new one
        if (!targetRow) {
            addSrfItemRow();
            const updatedRows = container.querySelectorAll('.srf-item-row');
            targetRow = updatedRows[updatedRows.length - 1];
        }

        if (targetRow) {
            setRowSelectedItem(targetRow, item);
            targetRow.classList.add('ring-2', 'ring-cyan-400', 'bg-cyan-50/50');
            setTimeout(() => targetRow.classList.remove('ring-2', 'ring-cyan-400', 'bg-cyan-50/50'), 1500);
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        clearSrfTopSearch();
    }

    // ROW-LEVEL SEARCH: Autocomplete Combobox
    function handleSrfRowItemSearch(input) {
        const container = input.closest('.srf-row-item-container');
        if (!container) return;

        const query = (input.value || '').trim().toLowerCase();
        const clearBtn = container.querySelector('.srf-item-search-clear');
        const select = container.querySelector('.srf-row-item-select');
        const row = container.closest('.srf-item-row');

        if (clearBtn) clearBtn.classList.toggle('hidden', input.value.trim().length === 0);

        // If user is typing new search query, clear current item ID and availability badge
        if (select && select.value) {
            const currentItem = srfInventoryStock.find(i => String(i.id) === String(select.value));
            const expectedText = currentItem ? `${currentItem.model} • ${currentItem.location} [${currentItem.qty} pcs]` : '';
            if (input.value !== expectedText) {
                select.value = '';
                resetRowAvailabilityBadge(row);
            }
        }

        renderSrfRowResults(container, query);
    }

    function showSrfRowItemDropdown(input) {
        const container = input.closest('.srf-row-item-container');
        if (!container) return;
        const query = (input.value || '').trim().toLowerCase();
        renderSrfRowResults(container, query);
    }

    function renderSrfRowResults(container, query) {
        const resultsBox = container.querySelector('.srf-row-results-dropdown');
        if (!resultsBox) return;

        // Dismiss other row dropdowns
        document.querySelectorAll('.srf-row-results-dropdown').forEach(b => {
            if (b !== resultsBox) b.classList.add('hidden');
        });
        const topResults = document.getElementById('srfTopSearchResults');
        if (topResults) topResults.classList.add('hidden');

        const q = (query || '').toLowerCase();
        let matches = srfInventoryStock;
        if (q.length > 0) {
            matches = srfInventoryStock.filter(item => item.search.includes(q));
        }

        if (matches.length === 0) {
            resultsBox.innerHTML = `<div class="p-3 text-center text-xs text-slate-400 italic">No matching warehouse items found.</div>`;
        } else {
            resultsBox.innerHTML = `
                <div class="px-2.5 py-1 bg-slate-50 text-[10px] font-bold text-slate-500 border-b border-slate-100 flex items-center justify-between">
                    <span>${q.length > 0 ? matches.length + ' matching' : 'All available items (' + matches.length + ')'}</span>
                    <span class="text-cyan-600 font-semibold">Select item</span>
                </div>
                ${matches.slice(0, 35).map(item => `
                    <div class="p-2 hover:bg-cyan-50/70 cursor-pointer flex items-center justify-between text-xs transition-colors group"
                         onclick="selectSrfRowItem(this, '${item.id}')">
                        <div class="min-w-0 pr-2">
                            <div class="font-bold text-slate-800 group-hover:text-cyan-700 truncate">${escapeHtml(item.model)}</div>
                            <div class="text-[11px] text-slate-500 font-mono-code flex flex-wrap items-center gap-1.5 mt-0.5">
                                ${item.mfr ? '<span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-semibold">' + escapeHtml(item.mfr) + '</span>' : ''}
                                <span>Bay: <strong class="text-slate-700">${escapeHtml(item.location)}</strong></span>
                                ${item.sqm > 0 ? '<span class="text-slate-400">• ' + item.sqm + ' sqm</span>' : ''}
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono-code ${item.qty > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                ${item.qty} pcs
                            </span>
                        </div>
                    </div>
                `).join('')}
            `;
        }
        resultsBox.classList.remove('hidden');
    }

    function selectSrfRowItem(el, itemId) {
        const container = el.closest('.srf-row-item-container');
        if (!container) return;
        const row = container.closest('.srf-item-row');
        const item = srfInventoryStock.find(i => String(i.id) === String(itemId));
        if (!row || !item) return;

        setRowSelectedItem(row, item);
    }

    function setRowSelectedItem(row, item) {
        const input = row.querySelector('.srf-item-search-input');
        const select = row.querySelector('.srf-row-item-select');
        const clearBtn = row.querySelector('.srf-item-search-clear');
        const resultsBox = row.querySelector('.srf-row-results-dropdown');

        if (input) {
            input.value = `${item.model} • ${item.location} [${item.qty} pcs]`;
        }
        if (select) {
            select.value = item.id;
        }
        if (clearBtn) {
            clearBtn.classList.remove('hidden');
        }
        if (resultsBox) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
        }

        updateRowAvailabilityBadge(row, item);
        checkSrfRowStockSufficiency(row.querySelector('.srf-item-qty-input'));
    }

    function updateRowAvailabilityBadge(row, item) {
        const qtyVal = row.querySelector('.srf-avail-qty-val');
        const sqmVal = row.querySelector('.srf-avail-sqm-val');
        const badge = row.querySelector('.srf-row-avail-badge');

        if (qtyVal) {
            qtyVal.textContent = `${item.qty} pcs`;
            qtyVal.classList.remove('text-slate-600', 'text-rose-600', 'text-emerald-700');
            qtyVal.classList.add(item.qty > 0 ? 'text-emerald-700' : 'text-rose-600');
        }
        if (sqmVal) {
            sqmVal.textContent = item.sqm > 0 ? `${item.sqm} sqm` : '—';
        }
        if (badge) {
            badge.classList.remove('bg-slate-100/90', 'border-slate-200');
            badge.classList.add('bg-amber-50/80', 'border-amber-200');
        }
    }

    function resetRowAvailabilityBadge(row) {
        if (!row) return;
        const qtyVal = row.querySelector('.srf-avail-qty-val');
        const sqmVal = row.querySelector('.srf-avail-sqm-val');
        const badge = row.querySelector('.srf-row-avail-badge');

        if (qtyVal) {
            qtyVal.textContent = '—';
            qtyVal.className = 'srf-avail-qty-val font-bold text-slate-600';
        }
        if (sqmVal) {
            sqmVal.textContent = '—';
            sqmVal.className = 'srf-avail-sqm-val font-semibold text-slate-500';
        }
        if (badge) {
            badge.className = 'srf-row-avail-badge w-full py-1.5 px-2 rounded-lg bg-slate-100/90 border border-slate-200 text-center transition-all flex items-center justify-center gap-1.5 font-mono-code text-[11px]';
        }
    }

    function checkSrfRowStockSufficiency(qtyInput) {
        if (!qtyInput) return;
        const row = qtyInput.closest('.srf-item-row');
        if (!row) return;

        const select = row.querySelector('.srf-row-item-select');
        const badge = row.querySelector('.srf-row-avail-badge');
        if (!select || !select.value || !badge) return;

        const item = srfInventoryStock.find(i => String(i.id) === String(select.value));
        if (!item) return;

        const reqQty = parseInt(qtyInput.value) || 0;
        if (reqQty > item.qty) {
            badge.classList.remove('bg-amber-50/80', 'border-amber-200');
            badge.classList.add('bg-rose-50', 'border-rose-300', 'text-rose-800');
            badge.title = `Requested quantity (${reqQty}) exceeds available warehouse stock (${item.qty} pcs). System will flag for PR & MRR.`;
        } else {
            badge.classList.remove('bg-rose-50', 'border-rose-300', 'text-rose-800');
            badge.classList.add('bg-amber-50/80', 'border-amber-200');
            badge.title = `Stock is available in warehouse (${item.qty} pcs).`;
        }
    }

    function clearSrfRowItem(btn) {
        const container = btn.closest('.srf-row-item-container');
        if (!container) return;
        const row = container.closest('.srf-item-row');

        const input = container.querySelector('.srf-item-search-input');
        const select = container.querySelector('.srf-row-item-select');
        const resultsBox = container.querySelector('.srf-row-results-dropdown');

        if (input) {
            input.value = '';
            input.focus();
        }
        if (select) {
            select.value = '';
        }
        btn.classList.add('hidden');

        resetRowAvailabilityBadge(row);
        renderSrfRowResults(container, '');
    }

    function filterSrfStockItems(query) {
        handleSrfTopSearch(query);
    }

    function addSrfItemRow() {
        const container = document.getElementById('srfItemsContainer');
        if (!container) return;

        const firstRow = container.querySelector('.srf-item-row');
        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);
        const currentIndex = srfItemIndex++;
        newRow.setAttribute('data-index', currentIndex);

        // Reset values and update input names
        const qtyInput = newRow.querySelector('input[name*="[quantity]"], input[name="quantity"]');
        if (qtyInput) {
            qtyInput.name = `items[${currentIndex}][quantity]`;
            qtyInput.value = 10;
        }

        const uomSelect = newRow.querySelector('select[name*="[uom]"], select[name="uom"]');
        if (uomSelect) {
            uomSelect.name = `items[${currentIndex}][uom]`;
            uomSelect.value = 'PCS';
        }

        const itemSelect = newRow.querySelector('select.inventory-item-select, select[name*="inventory_item_id"]');
        if (itemSelect) {
            itemSelect.name = `items[${currentIndex}][inventory_item_id]`;
            itemSelect.value = '';
        }

        const itemSearchInput = newRow.querySelector('.srf-item-search-input');
        if (itemSearchInput) {
            itemSearchInput.value = '';
        }

        const itemClearBtn = newRow.querySelector('.srf-item-search-clear');
        if (itemClearBtn) {
            itemClearBtn.classList.add('hidden');
        }

        const itemResultsDropdown = newRow.querySelector('.srf-row-results-dropdown');
        if (itemResultsDropdown) {
            itemResultsDropdown.classList.add('hidden');
            itemResultsDropdown.innerHTML = '';
        }

        resetRowAvailabilityBadge(newRow);

        const remarksInput = newRow.querySelector('input[name*="[remarks]"], input[name="remarks"]');
        if (remarksInput) {
            remarksInput.name = `items[${currentIndex}][remarks]`;
            remarksInput.value = '';
        }

        container.appendChild(newRow);
        updateSrfItemsUI();

        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (itemSearchInput) {
            itemSearchInput.focus();
        }
    }

    function removeSrfItemRow(btn) {
        const container = document.getElementById('srfItemsContainer');
        if (!container) return;

        const row = btn.closest('.srf-item-row');
        if (!row) return;

        const totalRows = container.querySelectorAll('.srf-item-row').length;
        if (totalRows <= 1) return; // Keep at least one item

        row.remove();
        updateSrfItemsUI();
    }

    function updateSrfItemsUI() {
        const container = document.getElementById('srfItemsContainer');
        if (!container) return;

        const rows = container.querySelectorAll('.srf-item-row');
        const badge = document.getElementById('srfItemCountBadge');

        if (badge) {
            badge.textContent = `${rows.length} ${rows.length === 1 ? 'Item' : 'Items'}`;
        }

        rows.forEach((r, idx) => {
            const removeBtn = r.querySelector('.srf-remove-item-btn');
            if (removeBtn) {
                if (rows.length > 1) {
                    removeBtn.classList.remove('hidden');
                } else {
                    removeBtn.classList.add('hidden');
                }
            }
        });
    }

    // Dismiss search result dropdowns on outside click or Escape
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.srf-row-item-container')) {
            document.querySelectorAll('.srf-row-results-dropdown').forEach(d => d.classList.add('hidden'));
        }
        if (!e.target.closest('.srf-top-search-container')) {
            const topResults = document.getElementById('srfTopSearchResults');
            if (topResults) topResults.classList.add('hidden');
        }
        if (!e.target.closest('.item-search-container')) {
            document.querySelectorAll('.item-search-results').forEach(box => box.classList.add('hidden'));
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.srf-row-results-dropdown').forEach(d => d.classList.add('hidden'));
            const topResults = document.getElementById('srfTopSearchResults');
            if (topResults) topResults.classList.add('hidden');
            document.querySelectorAll('.item-search-results').forEach(box => box.classList.add('hidden'));
        }
    });

    // Validate SRF Form on submission: ensure each row has a valid inventory item
    document.addEventListener('DOMContentLoaded', function() {
        const srfForm = document.querySelector('form[action*="operations.installation.srf"]');
        if (srfForm) {
            srfForm.addEventListener('submit', function(e) {
                const rows = document.querySelectorAll('#srfItemsContainer .srf-item-row');
                for (const row of rows) {
                    const sel = row.querySelector('.srf-row-item-select, select.inventory-item-select');
                    const input = row.querySelector('.srf-item-search-input');
                    if (!sel || !sel.value) {
                        e.preventDefault();
                        if (input) {
                            input.focus();
                            input.classList.add('ring-2', 'ring-rose-500', 'border-rose-500');
                            setTimeout(() => input.classList.remove('ring-2', 'ring-rose-500', 'border-rose-500'), 2500);
                        }
                        alert('Please choose or search a valid inventory item description for each row.');
                        return false;
                    }
                }
            });
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

@if (Auth::user()->isItAdmin())
<!-- ============================================================= -->
<!-- IT ADMIN: DEPARTMENT ACCOUNTS & SECURITY MANAGEMENT CONSOLE    -->
<!-- ============================================================= -->
<div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                    IT ADMINISTRATION CONSOLE
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                    DEPARTMENT ACCOUNT DIRECTORY
                </span>
                <span class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold ml-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    IDENTITY &amp; ACCESS MANAGEMENT ACTIVE
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span>Department Account Management</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl leading-relaxed">
                Centrally manage personnel accounts, clearance privileges, and security roles across Warehouse, Sales, IT, and Logistics departments.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 self-start lg:self-auto shrink-0">
            <a 
                href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" 
                class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold whitespace-nowrap transition-all shadow-sm shadow-sky-500/20"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>+ Create Sales Account</span>
            </a>

            <a 
                href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" 
                class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold whitespace-nowrap transition-all shadow-sm shadow-blue-500/20"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>+ Create New User</span>
            </a>

            <a 
                href="{{ route('admin.settings.credentials', ['tab' => 'roles']) }}" 
                class="h-10 px-3.5 inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold whitespace-nowrap transition-colors border border-slate-200"
            >
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Manage Roles</span>
            </a>
        </div>
    </div>

    <!-- 4 Department Pillars -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Pillar 1: IT & Systems -->
        <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-purple-200 text-purple-900">IT &amp; SECURITY</span>
                    <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs font-mono-code">
                        {{ $adminCount }}
                    </div>
                </div>
                <h4 class="text-sm font-bold text-purple-950 mt-2">IT Administrators</h4>
                <p class="text-xs text-purple-800/80 mt-1 leading-relaxed">Full system governance, credentials access, and security audit rights.</p>
            </div>
            <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'role' => 'it-admin']) }}" class="mt-4 text-xs font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1">
                <span>View IT Accounts →</span>
            </a>
        </div>

        <!-- Pillar 2: Warehouse Operations -->
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-emerald-200 text-emerald-900">WAREHOUSE OPS</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs font-mono-code">
                        {{ $staffCount }}
                    </div>
                </div>
                <h4 class="text-sm font-bold text-emerald-950 mt-2">Floor &amp; Inventory Staff</h4>
                <p class="text-xs text-emerald-800/80 mt-1 leading-relaxed">Pallet receiving, stock movement, picking, testing, and barcode ops.</p>
            </div>
            <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'role' => 'warehouse-staff']) }}" class="mt-4 text-xs font-bold text-emerald-700 hover:text-emerald-900 flex items-center gap-1">
                <span>View Floor Staff →</span>
            </a>
        </div>

        <!-- Pillar 3: Sales Department -->
        <div class="p-4 rounded-2xl bg-sky-50/70 border border-sky-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-sky-200 text-sky-900">SALES &amp; REQUISITION</span>
                    <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs font-mono-code">
                        {{ $salesCount ?? 0 }}
                    </div>
                </div>
                <h4 class="text-sm font-bold text-sky-950 mt-2">Sales Executives</h4>
                <p class="text-xs text-sky-800/80 mt-1 leading-relaxed">Sales Service Orders (SSO), Stock Requisition Forms (SRF), and installation requests.</p>
            </div>
            <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'role' => 'sales-executive']) }}" class="mt-4 text-xs font-bold text-sky-700 hover:text-sky-900 flex items-center gap-1">
                <span>View Sales Accounts →</span>
            </a>
        </div>

        <!-- Pillar 4: Security Roles & RBAC -->
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-amber-200 text-amber-900">SECURITY RBAC</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs font-mono-code">
                        {{ $totalRoles }}
                    </div>
                </div>
                <h4 class="text-sm font-bold text-amber-950 mt-2">Roles &amp; Clearances</h4>
                <p class="text-xs text-amber-800/80 mt-1 leading-relaxed">Department permissions matrix, access policies, and session privileges.</p>
            </div>
            <a href="{{ route('admin.settings.credentials', ['tab' => 'roles']) }}" class="mt-4 text-xs font-bold text-amber-700 hover:text-amber-900 flex items-center gap-1">
                <span>Configure Roles →</span>
            </a>
        </div>

    </div>

</div>
@endif


@if (!Auth::user()->isTechnical())
<!-- TWO COLUMN SECTION: WAREHOUSE FACILITY OVERVIEW & SYSTEM TELEMETRY -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- RECENT USER ACCOUNTS SUMMARY (2 COLUMNS) -->
    <div class="lg:col-span-2 glass-panel bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>Authorized System Accounts</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono-code bg-slate-100 text-slate-600 border border-slate-200">Active</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Summary of warehouse operators and administrators</p>
            </div>

            @if (Auth::user()->canAccessAdminCredentials())
                <a href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 group">
                    <span>Manage Accounts</span>
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50/50">
                        <th class="py-3 pl-3 font-mono-code">User Details</th>
                        <th class="py-3 px-2 font-mono-code">Clearance / Role</th>
                        <th class="py-3 px-2 font-mono-code">Created</th>
                        <th class="py-3 text-right pr-3 font-mono-code">
                            {{ Auth::user()->canAccessAdminCredentials() ? 'Action' : 'Status' }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentUsers as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="py-3.5 pl-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $user->name }}</div>
                                        <div class="text-[11px] font-mono-code text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-2">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($user->roles as $role)
                                        @php
                                            $colorClasses = match($role->badge_color) {
                                                'purple' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'sky' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                'rose' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default => 'bg-blue-50 text-blue-700 border-blue-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $colorClasses }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">No roles assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3.5 px-2 text-xs text-slate-500 font-mono-code">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 text-right pr-3">
                                @if (Auth::user()->canAccessAdminCredentials())
                                    <a href="{{ route('admin.settings.credentials', ['tab' => 'users', 'search' => $user->email]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">
                                        <span>Manage</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono-code bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Active
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-xs text-slate-400">
                                No user accounts registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ROLES & SYSTEM TELEMETRY (1 COLUMN) -->
    <div class="space-y-6">
        
        <!-- Warehouse Facility Diagnostics -->
        <div class="glass-panel bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono-code">Facility Telemetry</h3>
                <span class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    ONLINE
                </span>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Warehouse Terminal</span>
                    <span class="font-mono-code font-semibold text-slate-800">WMS-ADM-01 (Main Hub)</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Facility Network</span>
                    <span class="font-mono-code font-semibold text-emerald-600">Gigabit Mesh (Active)</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Barcode Scanners</span>
                    <span class="font-mono-code font-semibold text-slate-800">12 Connected</span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-500">Security Audit</span>
                    <span class="font-mono-code font-semibold text-blue-600">Zero Violations</span>
                </div>
            </div>
        </div>

        @if (Auth::user()->canAccessAdminCredentials())
            <!-- Quick Access to Admin Credentials Modules (IT Admin Only) -->
            <div class="glass-panel bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono-code">Admin Quick Links</h3>
                    <a href="{{ route('admin.settings.credentials') }}" class="text-xs text-blue-600 hover:underline font-semibold">Open Console</a>
                </div>

                <div class="mt-3.5 space-y-2">
                    <a href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 text-slate-700 hover:text-blue-600 transition-colors border border-transparent hover:border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="font-semibold">User Accounts & Credentials</span>
                        </div>
                        <span class="font-mono-code text-[11px] text-slate-400">Manage →</span>
                    </a>

                    <a href="{{ route('admin.settings.credentials', ['tab' => 'roles']) }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 text-slate-700 hover:text-amber-600 transition-colors border border-transparent hover:border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Roles & Clearance Levels</span>
                        </div>
                        <span class="font-mono-code text-[11px] text-slate-400">{{ $totalRoles }} Roles →</span>
                    </a>

                    <a href="{{ route('admin.settings.credentials', ['tab' => 'credentials']) }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 text-slate-700 hover:text-purple-600 transition-colors border border-transparent hover:border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Admin Profile & Password</span>
                        </div>
                        <span class="font-mono-code text-[11px] text-slate-400">Security →</span>
                    </a>
                </div>
            </div>
        @else
            <!-- Warehouse Floor Operations Quick Links (Warehouse Admin) -->
            <div class="glass-panel bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono-code">Warehouse Operations</h3>
                    <span class="text-[11px] font-mono-code text-slate-500">Floor Node</span>
                </div>

                <div class="mt-3.5 space-y-2">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-800">Storage & Pallet Bays</span>
                        </div>
                        <span class="font-mono-code text-[11px] text-emerald-600 font-semibold">1,280 Bays</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-800">Floor Picking Staff</span>
                        </div>
                        <span class="font-mono-code text-[11px] text-blue-600 font-semibold">{{ $staffCount }} Active</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-800">Clearance Authority</span>
                        </div>
                        <span class="font-mono-code text-[11px] text-purple-600 font-semibold">Warehouse Admin</span>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
@endif
@endsection

@section('modals')
@if (Auth::user()->isItAdmin())
<!-- ========================================== -->
<!-- MODAL: ACTIVE ONLINE SESSIONS (IT ADMIN)   -->
<!-- ========================================== -->
<div id="onlineUsersModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm hidden animate-fadeIn" onclick="if(event.target === this) closeOnlineUsersModal()">
    <div class="relative w-full max-w-2xl rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-7 max-h-[90vh] flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 relative">
                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-bold text-slate-900">Active Online Users</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono-code font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            {{ $onlineUsersCount ?? 1 }} Active
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Real-time authenticated sessions active within the last 5 minutes</p>
                </div>
            </div>
            <button type="button" onclick="closeOnlineUsersModal()" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer" title="Close Modal">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body / Users Table -->
        <div class="mt-4 flex-1 overflow-y-auto divide-y divide-slate-100 pr-1">
            @if(isset($onlineUsers) && $onlineUsers->isNotEmpty())
                <div class="space-y-2.5">
                    @foreach($onlineUsers as $online)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-slate-100/60 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="relative shrink-0">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-800 to-slate-950 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                                        {{ strtoupper(substr($online->name, 0, 2)) }}
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-sm text-slate-900 truncate">{{ $online->name }}</span>
                                        @if($online->is_current)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                                YOU
                                            </span>
                                        @endif
                                        @if(in_array($online->role_slug, ['it-admin', 'warehouse-admin']))
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                                {{ $online->role }}
                                            </span>
                                        @elseif(in_array($online->role_slug, ['warehouse-staff']))
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                {{ $online->role }}
                                            </span>
                                        @elseif(in_array($online->role_slug, ['sales-executive']))
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-sky-100 text-sky-700 border border-sky-200">
                                                {{ $online->role }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $online->role }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 truncate">{{ $online->email }}</p>
                                </div>
                            </div>
                            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center text-xs shrink-0 pl-12 sm:pl-0">
                                <span class="font-mono-code text-[11px] text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    IP: {{ $online->ip_address }}
                                </span>
                                <span class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $online->last_activity->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500 text-sm">
                    No active sessions found.
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="pt-4 mt-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-mono-code">
                Auto-refreshed on reload • Inactivity timeout: 5m
            </span>
            <button type="button" onclick="closeOnlineUsersModal()" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer">
                Close
            </button>
        </div>

    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    function openOnlineUsersModal() {
        const modal = document.getElementById('onlineUsersModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeOnlineUsersModal() {
        const modal = document.getElementById('onlineUsersModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
</script>
@endpush
