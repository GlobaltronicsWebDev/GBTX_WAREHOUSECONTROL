@extends('layouts.admin')

@section('title', 'ADS Workflow Roles & Access Control | Globaltronics ICS')

@section('content')
<div class="space-y-6">

    <!-- Top Header Card -->
    <div class="glass-panel bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-purple-100 text-purple-900 border border-purple-200">
                        ADMIN ACCESS CONTROL
                    </span>
                    <span class="text-xs text-slate-400">• Role-Based Workflow Governance</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span>ADS Workflow Role Assignments</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Assign specific workflow step roles (Jovy, Fred, Owen, Macy, Badet, King, Sales, Content, IT, Accounting) and Administrator privileges to system users.
                </p>
            </div>

            <a href="{{ route('admin.sales-orders.index') }}" 
               class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs shrink-0 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

    <!-- Role Legend Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach ($workflowRoles as $rSlug => $rInfo)
            <div class="p-3.5 bg-white rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-mono-code text-[10px] font-bold uppercase text-slate-400">{{ $rSlug }}</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <span class="text-xs font-bold text-slate-900 block truncate" title="{{ $rInfo['name'] }}">{{ $rInfo['name'] }}</span>
                <span class="text-[10px] text-slate-500 line-clamp-1 mt-0.5" title="{{ $rInfo['description'] }}">{{ $rInfo['description'] }}</span>
            </div>
        @endforeach
    </div>

    <!-- Users Table with Role Edit Forms -->
    <div class="glass-panel bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono-code">User Account Role Mappings</h2>
                <span class="text-xs font-mono-code font-bold text-purple-800 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200">
                    {{ $users->total() }} Accounts
                </span>
            </div>

            <form method="GET" action="{{ route('admin.sales-orders.roles') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..." class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white">
                <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold">Search</button>
                @if ($search)
                    <a href="{{ route('admin.sales-orders.roles') }}" class="px-2 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-xs">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-mono-code tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">System Administrator</th>
                        <th class="py-3 px-4">Assigned ADS Workflow Roles</th>
                        <th class="py-3 px-4 text-center">Save Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <form method="POST" action="{{ route('admin.sales-orders.roles.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- User Info -->
                                <td class="py-3 px-4 align-top">
                                    <span class="font-bold text-slate-900 block text-sm">{{ $user->name }}</span>
                                    <span class="text-xs text-slate-400 font-mono-code block">{{ $user->email }}</span>
                                </td>

                                <!-- is_admin Flag -->
                                <td class="py-3 px-4 align-top">
                                    <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-xl border border-slate-200 hover:border-purple-300 transition-colors">
                                        <input type="checkbox" name="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }} class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                        <span class="font-bold text-slate-800 text-xs">is_admin (All Queues &amp; Roles)</span>
                                    </label>
                                </td>

                                <!-- ADS Workflow Roles Checkboxes -->
                                <td class="py-3 px-4 align-top">
                                    <div class="flex flex-wrap gap-2 max-w-xl">
                                        @foreach ($workflowRoles as $slug => $info)
                                            @php
                                                $hasRole = $user->hasRole($slug);
                                            @endphp
                                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-semibold cursor-pointer transition-colors {{ $hasRole ? 'bg-purple-50 border-purple-300 text-purple-900' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                <input type="checkbox" name="roles[]" value="{{ $slug }}" {{ $hasRole ? 'checked' : '' }} class="rounded border-slate-300 text-purple-600 focus:ring-purple-500 text-xs">
                                                <span>{{ ucfirst($slug) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Submit Button -->
                                <td class="py-3 px-4 text-center align-middle">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition-colors">
                                        Save
                                    </button>
                                </td>
                            </tr>
                        </form>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
