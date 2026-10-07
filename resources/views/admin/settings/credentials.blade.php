@extends('layouts.admin')

@section('title', 'Admin Operations Console | Globaltronics ICS')

@section('content')
<!-- Page Header: Admin Operations Console (Exactly as in wireframe & screenshot) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
    <div>
        <div class="flex items-center gap-2 text-xs font-mono-code text-blue-600 mb-1">
            <span>TERMINAL ID: WMS-ADM-01</span>
            <span>•</span>
            <span class="text-emerald-600 font-semibold">SESSION ACTIVE</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Admin Operations Console</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Globaltronics Inventory Control System • User administration, security roles, and credential management.
        </p>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex items-center gap-2.5">
        <button type="button" onclick="openCreateUserModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.01]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Create Account</span>
        </button>

        <button type="button" onclick="openCreateRoleModal()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs sm:text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Manage Roles</span>
        </button>
    </div>
</div>

<!-- LIVE METRICS CARDS (Five Tiles including Sales) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    
    <!-- Total Users -->
    <div onclick="switchTab('users')" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-400 hover:shadow-md transition-all cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Accounts</span>
            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-slate-900 font-mono-code">{{ $totalUsers }}</span>
            <span class="text-xs text-emerald-600 font-semibold">Registered</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Active credentials across all warehouse departments</p>
    </div>

    <!-- Administrators -->
    <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-purple-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Admins & Supervisors</span>
            <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-purple-700 font-mono-code">{{ $adminCount }}</span>
            <span class="text-xs text-purple-600 font-semibold">Elevated</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">IT Admin & Warehouse Admin clearance holders</p>
    </div>

    <!-- Warehouse Staff -->
    <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Warehouse Staff</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-emerald-700 font-mono-code">{{ $staffCount }}</span>
            <span class="text-xs text-emerald-600 font-semibold">Floor Ops</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Stock picking, pallet dispatch, and barcode scanning</p>
    </div>

    <!-- Sales & Accounts -->
    <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-sky-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sales & Requisition</span>
            <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-sky-700 font-mono-code">{{ $salesCount ?? 0 }}</span>
            <span class="text-xs text-sky-600 font-semibold">Accounts</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Initiates Sales Service Orders (SSO) & requisitions</p>
    </div>

    <!-- Roles Configured -->
    <div onclick="switchTab('roles')" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-amber-400 hover:shadow-md transition-all cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Security Roles</span>
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-amber-700 font-mono-code">{{ $totalRoles }}</span>
            <span class="text-xs text-amber-600 font-semibold">Profiles</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Custom clearance levels & RBAC permissions</p>
    </div>

</div>

<!-- TOP TABS NAVIGATION -->
<div class="flex items-center gap-2 border-b border-slate-200 pb-px overflow-x-auto">
    <!-- Tab 1: Overview Console -->
    <button 
        type="button" 
        onclick="switchTab('overview')" 
        id="tabBtn-overview"
        class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'overview' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        <span>Console Overview</span>
    </button>

    <!-- Tab 2: User Accounts -->
    <button 
        type="button" 
        onclick="switchTab('users')" 
        id="tabBtn-users"
        class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'users' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span>User Accounts</span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-mono-code {{ $activeTab === 'users' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
            {{ $users->total() }}
        </span>
    </button>

    <!-- Tab 3: Roles & Clearances -->
    <button 
        type="button" 
        onclick="switchTab('roles')" 
        id="tabBtn-roles"
        class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'roles' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span>Roles & Clearances</span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-mono-code {{ $activeTab === 'roles' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
            {{ $roles->count() }}
        </span>
    </button>

    <!-- Tab 4: Admin Security & Password -->
    <button 
        type="button" 
        onclick="switchTab('credentials')" 
        id="tabBtn-credentials"
        class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'credentials' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>
        <span>Admin Security & Password</span>
    </button>
</div>

<!-- ========================================== -->
<!-- TAB PANEL 1: CONSOLE OVERVIEW              -->
<!-- ========================================== -->
<div id="panel-overview" class="tab-panel {{ $activeTab === 'overview' ? '' : 'hidden' }} space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- RECENT USERS TABLE (2 COLUMNS) -->
        <div class="lg:col-span-2 glass-panel bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Recent User Accounts</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-mono-code bg-slate-100 text-slate-600 border border-slate-200">Latest</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Quick access to newly registered or modified credentials</p>
                </div>
                <button type="button" onclick="switchTab('users')" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
                    <span>View All Users</span>
                    <span>→</span>
                </button>
            </div>

            <!-- Table of Recent Accounts -->
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">
                            <th class="pb-2 font-mono-code">User Details</th>
                            <th class="pb-2 font-mono-code">Clearance / Role</th>
                            <th class="pb-2 font-mono-code">Created</th>
                            <th class="pb-2 font-mono-code text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentUsers as $recentUser)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 pr-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                            {{ strtoupper(substr($recentUser->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $recentUser->name }}</div>
                                            <div class="text-[11px] font-mono-code text-slate-500">{{ $recentUser->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-2">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($recentUser->roles as $role)
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
                                    {{ $recentUser->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3.5 text-right pr-1">
                                    <button 
                                        type="button" 
                                        onclick="openEditUserModal({{ json_encode([
                                            'id' => $recentUser->id,
                                            'name' => $recentUser->name,
                                            'email' => $recentUser->email,
                                            'role_ids' => $recentUser->roles->pluck('id')->toArray(),
                                        ]) }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors"
                                    >
                                        <span>Manage</span>
                                    </button>
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

        <!-- ROLES & SYSTEM SUMMARY (1 COLUMN) -->
        <div class="space-y-6">
            
            <!-- System Roles Directory -->
            <div class="glass-panel bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">Role Directory</h3>
                    <button type="button" onclick="switchTab('roles')" class="text-xs text-blue-600 hover:underline font-semibold">Manage All</button>
                </div>

                <div class="mt-3.5 space-y-2.5">
                    @foreach ($roles as $role)
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
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 hover:bg-slate-100/70 transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $colorClasses }}">
                                    {{ $role->name }}
                                </span>
                                @if ($role->is_system)
                                    <span title="Protected System Role">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-mono-code text-slate-600 shrink-0">
                                {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Facility & Security Clearance Info -->
            <div class="p-5 rounded-2xl bg-blue-50/70 border border-blue-200 text-xs shadow-sm">
                <div class="flex items-center gap-2.5 text-blue-800 mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold tracking-wide uppercase font-mono-code">Security Policy Notice</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Role modifications take effect immediately upon session renewal. Only authorized IT Administrators are eligible to create system-level accounts or adjust database security clearance assignments.
                </p>
                <div class="mt-4 pt-3 border-t border-blue-200/80 flex items-center justify-between text-[11px] font-mono-code text-slate-500">
                    <span>Facility: CEB-01</span>
                    <span class="text-emerald-700 font-semibold">Audit: Active</span>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- TAB PANEL 2: USER ACCOUNTS & MANAGEMENT    -->
<!-- ========================================== -->
<div id="panel-users" class="tab-panel {{ $activeTab === 'users' ? '' : 'hidden' }} space-y-4">
    
    <!-- Action Bar & Filter -->
    <div class="glass-panel bg-white p-4 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-3 shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('admin.settings.credentials') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
            <input type="hidden" name="tab" value="users">

            <!-- Search Input -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search user by name or email address..." 
                    class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all"
                />
            </div>

            <!-- Role Filter Dropdown -->
            <div class="w-full sm:w-60">
                <select name="role" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">All Roles ({{ $roles->count() }})</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $selectedRole == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs sm:text-sm font-semibold transition-colors border border-slate-200">
                Filter
            </button>

            @if ($search || $selectedRole)
                <a href="{{ route('admin.settings.credentials', ['tab' => 'users']) }}" class="w-full sm:w-auto px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold text-center transition-colors">
                    Reset
                </a>
            @endif
        </form>

        <!-- Create User & Sales Account Buttons -->
        <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto">
            <button type="button" onclick="openCreateSalesModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-500/20 transition-all hover:scale-[1.01]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>+ Create Sales Account</span>
            </button>

            <button type="button" onclick="openCreateUserModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.01]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Create New User</span>
            </button>
        </div>
    </div>

    <!-- USERS DATA TABLE -->
    <div class="glass-panel bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50 border-b border-slate-200">
                        <th class="py-3.5 px-4">Account User</th>
                        <th class="py-3.5 px-4">Assigned Roles</th>
                        <th class="py-3.5 px-4">Access Clearance</th>
                        <th class="py-3.5 px-4">Date Added</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $userItem)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            <!-- User Name & Email -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 shadow-sm">
                                        {{ strtoupper(substr($userItem->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $userItem->name }}</span>
                                            @if (Auth::id() === $userItem->id)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">YOU</span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-mono-code text-slate-500">{{ $userItem->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Assigned Roles Badges -->
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($userItem->roles as $role)
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
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold border {{ $colorClasses }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">No role assigned</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Access Clearance Level -->
                            <td class="py-4 px-4">
                                @if ($userItem->hasRole('it-admin'))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold font-mono-code bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                        FULL ROOT ACCESS
                                    </span>
                                @elseif ($userItem->hasRole('warehouse-admin'))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold font-mono-code bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        ELEVATED WMS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold font-mono-code bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        STANDARD OPS
                                    </span>
                                @endif
                            </td>

                            <!-- Date Added -->
                            <td class="py-4 px-4 font-mono-code text-slate-500 text-xs">
                                {{ $userItem->created_at->format('M d, Y') }}
                            </td>

                            <!-- Actions: Edit & Delete -->
                            <td class="py-4 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit User Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditUserModal({{ json_encode([
                                            'id' => $userItem->id,
                                            'name' => $userItem->name,
                                            'email' => $userItem->email,
                                            'role_ids' => $userItem->roles->pluck('id')->toArray(),
                                        ]) }})"
                                        class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Edit User & Roles"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Delete User Button (with modal confirmation) -->
                                    @if (Auth::id() !== $userItem->id)
                                        <button 
                                            type="button" 
                                            onclick="openDeleteUserModal({{ json_encode([
                                                'id' => $userItem->id,
                                                'name' => $userItem->name,
                                                'email' => $userItem->email,
                                            ]) }})"
                                            class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Delete User"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-700">No user accounts found matching your query</p>
                                <p class="text-xs text-slate-400 mt-1">Try clearing filters or search keywords.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- TAB PANEL 3: ROLES & CLEARANCES            -->
<!-- ========================================== -->
<div id="panel-roles" class="tab-panel {{ $activeTab === 'roles' ? '' : 'hidden' }} space-y-4">
    
    <!-- Roles Header Toolbar -->
    <div class="glass-panel bg-white p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm border border-slate-200">
        <div>
            <h2 class="text-base font-bold text-slate-900">Access Roles & Permission Profiles</h2>
            <p class="text-xs text-slate-500">Configure permission sets, create department profiles, and map clearances</p>
        </div>

        <button type="button" onclick="openCreateRoleModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.01]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Create New Role</span>
        </button>
    </div>

    <!-- ROLES GRID CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($roles as $roleItem)
            @php
                $colorClasses = match($roleItem->badge_color) {
                    'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500'],
                    'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200', 'dot' => 'bg-sky-500'],
                    'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
                    'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
                    default => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
                };
            @endphp
            <div class="glass-card bg-white rounded-2xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between hover:border-slate-300 transition-all group">
                <div>
                    <!-- Card Top: Name & System Badge -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $colorClasses['dot'] }}"></span>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                {{ $roleItem->name }}
                            </h3>
                        </div>
                        @if ($roleItem->is_system)
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                System Protected
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200">
                                Custom Role
                            </span>
                        @endif
                    </div>

                    <!-- Role Slug -->
                    <p class="text-xs font-mono-code text-slate-400 mt-1">
                        slug: {{ $roleItem->slug }}
                    </p>

                    <!-- Role Description -->
                    <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                        {{ $roleItem->description ?: 'No detailed clearance description configured for this role.' }}
                    </p>
                </div>

                <!-- Card Bottom: User Count & Actions -->
                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-bold text-slate-800">{{ $roleItem->users_count }}</span>
                        <span>user accounts</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <!-- Edit Role Button -->
                        <button 
                            type="button" 
                            onclick="openEditRoleModal({{ json_encode([
                                'id' => $roleItem->id,
                                'name' => $roleItem->name,
                                'slug' => $roleItem->slug,
                                'description' => $roleItem->description,
                                'badge_color' => $roleItem->badge_color,
                                'is_system' => $roleItem->is_system,
                            ]) }})"
                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                            title="Edit Role & Permissions"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>

                        <!-- Delete Role Button -->
                        @if (! $roleItem->is_system)
                            <button 
                                type="button" 
                                onclick="openDeleteRoleModal({{ json_encode([
                                    'id' => $roleItem->id,
                                    'name' => $roleItem->name,
                                    'users_count' => $roleItem->users_count,
                                ]) }})"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                title="Delete Role"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>

<!-- ========================================== -->
<!-- TAB PANEL 4: ADMIN CREDENTIALS & SECURITY  -->
<!-- ========================================== -->
<div id="panel-credentials" class="tab-panel {{ $activeTab === 'credentials' ? '' : 'hidden' }} space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: CREDENTIALS FORMS -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Form 1: Profile Credentials (Name & Email) -->
            <div class="glass-panel bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-slate-200">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Administrator Profile Identity</h2>
                        <p class="text-xs text-slate-500">Update your account name and system contact email</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.credentials.profile') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Administrator Name <span class="text-amber-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Login Email Address <span class="text-amber-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                        />
                        <p class="text-[11px] text-slate-500 mt-1">
                            This email serves as your primary login identifier for warehouse operations.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 transition-all">
                            Update Identity Credentials
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form 2: Password Rotation -->
            <div class="glass-panel bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-slate-200">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Change Admin Password</h2>
                        <p class="text-xs text-slate-500">Rotate authentication secrets with current password confirmation</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.credentials.password') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Current Password <span class="text-amber-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="current_password" 
                            id="current_password" 
                            required 
                            placeholder="••••••••••••" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                New Password <span class="text-amber-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required 
                                minlength="8" 
                                placeholder="At least 8 characters" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                            />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Confirm New Password <span class="text-amber-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="password_confirmation" 
                                required 
                                minlength="8" 
                                placeholder="Confirm matching password" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                            />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-amber-500/20 transition-all">
                            Update Admin Password
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- RIGHT 1 COL: SECURITY SUMMARY & POLICIES -->
        <div class="space-y-6">

            <!-- User Summary Card -->
            <div class="glass-card bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center gap-3.5 pb-4 border-b border-slate-200">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center font-extrabold text-white text-base shadow-sm">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $user->name }}</h3>
                        <p class="text-xs font-mono-code text-slate-500">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[11px] uppercase font-bold tracking-wider mb-1">Clearances Assigned</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($user->roles as $role)
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $colorClasses }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-slate-500">
                        <span>Account Role Status:</span>
                        <span class="font-bold text-emerald-600 font-mono-code">ACTIVE</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-500">
                        <span>Member Since:</span>
                        <span class="font-mono-code">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Globaltronics IT Security Guidelines -->
            <div class="glass-card bg-slate-50/70 rounded-2xl p-5 border border-slate-200 text-xs space-y-3">
                <div class="flex items-center gap-2 font-bold text-slate-900">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Credential Security Guidelines</span>
                </div>
                <ul class="space-y-2 text-slate-600 list-disc list-inside text-[11px] leading-relaxed">
                    <li>Minimum 8 characters with alphanumeric & special symbols.</li>
                    <li>Credentials rotate automatically after 90 days of inactivity.</li>
                    <li>Always terminate your session when leaving the terminal.</li>
                </ul>
            </div>

        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODALS: USER ACCOUNTS (CREATE, EDIT, DELETE)-->
<!-- ========================================== -->

@php
    $salesRoleObj = $roles->firstWhere('slug', 'sales-executive');
@endphp

<!-- Modal 0: Dedicated Create Sales Account Modal -->
<div id="createSalesModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-sky-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-sky-50 text-sky-600 border border-sky-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Create Sales & Account Executive</h3>
                    <p class="text-xs text-slate-500">Initiates Sales Service Orders (SSO) & project requisitions</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('createSalesModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mt-3 p-3 rounded-xl bg-sky-50/70 border border-sky-200 text-xs text-sky-900 flex items-start gap-2">
            <svg class="w-4 h-4 text-sky-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>This account will be assigned the <strong>Sales / Account Executive</strong> role to prepare Sales Service Orders (SSO) and Stock Requisition Forms (SRF) for warehouse installation projects.</span>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-4">
            @csrf

            @if ($salesRoleObj)
                <input type="hidden" name="roles[]" value="{{ $salesRoleObj->id }}" />
            @endif

            <div>
                <label for="create_sales_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Sales Representative Name <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="create_sales_name" 
                    required 
                    placeholder="e.g. Ariel Moro" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-sky-500"
                />
            </div>

            <div>
                <label for="create_sales_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Sales Work Email <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="create_sales_email" 
                    required 
                    placeholder="e.g. ariel.moro@globaltronics.net" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-sky-500 font-mono-code"
                />
            </div>

            <div>
                <label for="create_sales_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Temporary Password <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="create_sales_password" 
                    required 
                    minlength="8" 
                    placeholder="•••••••••••• (min 8 characters)" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-sky-500 font-mono-code"
                />
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-medium">Automatic Role Clearance:</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                    Sales / Account Executive
                </span>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createSalesModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-md shadow-sky-500/20 transition-all">
                    Register Sales Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 1: Create User Modal -->
<div id="createUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Create New User Account</h3>
                    <p class="text-xs text-slate-500">Provide personal employee details & assign initial security roles</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('createUserModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="create_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Full Name <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="create_name" 
                    required 
                    placeholder="e.g. John Doe" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="create_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Employee Work Email <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="create_email" 
                    required 
                    placeholder="e.g. j.doe@globaltronics.net" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <div>
                <label for="create_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Temporary Initial Password <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="create_password" 
                    required 
                    minlength="8" 
                    placeholder="•••••••••••• (min 8 characters)" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Assign Roles & Clearances
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-44 overflow-y-auto p-2 rounded-xl bg-slate-50 border border-slate-200">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="roles[]" 
                                value="{{ $role->id }}" 
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <div class="text-xs">
                                <span class="font-bold text-slate-900 block">{{ $role->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono-code">{{ $role->slug }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createUserModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all">
                    Create User Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit User & Clearances</h3>
                    <p class="text-xs text-slate-500">Update account credentials, reset password, or adjust assigned roles</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('editUserModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editUserForm" method="POST" action="" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Full Name <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="edit_name" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="edit_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Employee Work Email <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="edit_email" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <div>
                <label for="edit_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    New Password (Optional)
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="edit_password" 
                    minlength="8" 
                    placeholder="Leave blank to retain current password" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Assigned Roles & Clearances
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-44 overflow-y-auto p-2 rounded-xl bg-slate-50 border border-slate-200">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="roles[]" 
                                value="{{ $role->id }}" 
                                id="edit_role_{{ $role->id }}"
                                class="edit-role-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <div class="text-xs">
                                <span class="font-bold text-slate-900 block">{{ $role->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono-code">{{ $role->slug }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Delete User Modal -->
<div id="deleteUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="p-2.5 rounded-xl bg-rose-50 text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Delete User Account</h3>
                <p class="text-xs text-slate-500">Revoke access and purge credentials</p>
            </div>
        </div>

        <form id="deleteUserForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('DELETE')

            <p class="text-xs text-slate-600 leading-relaxed">
                Are you sure you want to permanently delete user account <strong id="deleteUserName" class="text-slate-900"></strong> (<span id="deleteUserEmail" class="font-mono-code text-slate-600"></span>)?
            </p>

            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                <strong>Warning:</strong> This will revoke all active terminal sessions, inventory credentials, and clearance assignments.
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('deleteUserModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-500/20 transition-all">
                    Permanently Delete
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALS: ROLES (CREATE, EDIT, DELETE)       -->
<!-- ========================================== -->

<!-- Modal 4: Create Role Modal -->
<div id="createRoleModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Create New Role</h3>
                    <p class="text-xs text-slate-500">Define a custom clearance profile for warehouse operations</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('createRoleModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.roles.store') }}" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="create_role_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Role Title <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="create_role_name" 
                    required 
                    placeholder="e.g. Forklift Operations Lead" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="create_role_desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Clearance Scope Description
                </label>
                <textarea 
                    name="description" 
                    id="create_role_desc" 
                    rows="3" 
                    placeholder="Describe authorized zones, bay clearances, or equipment authority..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            <div>
                <label for="create_badge_color" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Badge Palette Color <span class="text-amber-500">*</span>
                </label>
                <select name="badge_color" id="create_badge_color" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="blue">Blue (Standard Staff)</option>
                    <option value="emerald">Emerald (Operations & Picking)</option>
                    <option value="amber">Amber (Dispatch & Supervisor)</option>
                    <option value="purple">Purple (IT Root & Elevation)</option>
                    <option value="sky">Sky (Logistics Coordination)</option>
                    <option value="indigo">Indigo (Quality Inspection)</option>
                    <option value="rose">Rose (Security & Audit)</option>
                    <option value="teal">Teal (Inventory Receiving)</option>
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createRoleModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Edit Role Modal -->
<div id="editRoleModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Role & Clearances</h3>
                    <p class="text-xs text-slate-500">Update role title, description, and badge style</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('editRoleModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editRoleForm" method="POST" action="" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_role_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Role Title <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="edit_role_name" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="edit_role_desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Clearance Scope Description
                </label>
                <textarea 
                    name="description" 
                    id="edit_role_desc" 
                    rows="3" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            <div>
                <label for="edit_badge_color" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Badge Palette Color <span class="text-amber-500">*</span>
                </label>
                <select name="badge_color" id="edit_badge_color" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="blue">Blue (Standard Staff)</option>
                    <option value="emerald">Emerald (Operations & Picking)</option>
                    <option value="amber">Amber (Dispatch & Supervisor)</option>
                    <option value="purple">Purple (IT Root & Elevation)</option>
                    <option value="sky">Sky (Logistics Coordination)</option>
                    <option value="indigo">Indigo (Quality Inspection)</option>
                    <option value="rose">Rose (Security & Audit)</option>
                    <option value="teal">Teal (Inventory Receiving)</option>
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editRoleModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 6: Delete Role Modal -->
<div id="deleteRoleModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 animate-fadeIn">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="p-2.5 rounded-xl bg-rose-50 text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Delete Security Role</h3>
                <p class="text-xs text-slate-500">Permanently remove custom role profile</p>
            </div>
        </div>

        <form id="deleteRoleForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('DELETE')

            <p class="text-xs text-slate-600 leading-relaxed">
                Are you sure you want to delete role <strong id="deleteRoleName" class="text-slate-900"></strong>?
            </p>

            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                <strong>Requirement:</strong> Roles assigned to active users cannot be removed until users are reassigned to another role.
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('deleteRoleModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-500/20 transition-all">
                    Permanently Delete
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- JAVASCRIPT: TABS & MODALS MANAGEMENT       -->
<!-- ========================================== -->
<script>
    // Tab switching functionality
    function switchTab(tab) {
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // Reset all buttons style
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-500/20');
            btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
            const badge = btn.querySelector('span.rounded-full');
            if (badge) {
                badge.classList.remove('bg-white/20', 'text-white');
                badge.classList.add('bg-slate-100', 'text-slate-600', 'border', 'border-slate-200');
            }
        });

        // Activate selected panel
        const activePanel = document.getElementById('panel-' + tab);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        // Activate selected button style
        const activeBtn = document.getElementById('tabBtn-' + tab);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-500/20');
            const badge = activeBtn.querySelector('span.rounded-full');
            if (badge) {
                badge.classList.remove('bg-slate-100', 'text-slate-600', 'border', 'border-slate-200');
                badge.classList.add('bg-white/20', 'text-white');
            }
        }

        // Update URL query string without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }

    // Modal helpers
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            ['createSalesModal', 'createUserModal', 'editUserModal', 'deleteUserModal', 'createRoleModal', 'editRoleModal', 'deleteRoleModal'].forEach(closeModal);
        }
    });

    // Close on backdrop click
    ['createSalesModal', 'createUserModal', 'editUserModal', 'deleteUserModal', 'createRoleModal', 'editRoleModal', 'deleteRoleModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', (e) => {
                if (e.target === el) closeModal(id);
            });
        }
    });

    // User Modal Triggers
    function openCreateSalesModal() {
        openModal('createSalesModal');
    }

    function openCreateUserModal() {
        openModal('createUserModal');
    }

    function openEditUserModal(user) {
        const form = document.getElementById('editUserForm');
        form.action = `/admin/users/${user.id}`;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_password').value = '';

        // Reset and check assigned role checkboxes
        document.querySelectorAll('.edit-role-checkbox').forEach(cb => {
            cb.checked = user.role_ids.includes(parseInt(cb.value));
        });

        openModal('editUserModal');
    }

    function openDeleteUserModal(user) {
        const form = document.getElementById('deleteUserForm');
        form.action = `/admin/users/${user.id}`;
        document.getElementById('deleteUserName').textContent = user.name;
        document.getElementById('deleteUserEmail').textContent = user.email;
        openModal('deleteUserModal');
    }

    // Role Modal Triggers
    function openCreateRoleModal() {
        openModal('createRoleModal');
    }

    function openEditRoleModal(role) {
        const form = document.getElementById('editRoleForm');
        form.action = `/admin/roles/${role.id}`;
        document.getElementById('edit_role_name').value = role.name;
        document.getElementById('edit_role_desc').value = role.description || '';
        document.getElementById('edit_badge_color').value = role.badge_color;
        openModal('editRoleModal');
    }

    function openDeleteRoleModal(role) {
        const form = document.getElementById('deleteRoleForm');
        form.action = `/admin/roles/${role.id}`;
        document.getElementById('deleteRoleName').textContent = role.name;
        openModal('deleteRoleModal');
    }
</script>
@endsection
