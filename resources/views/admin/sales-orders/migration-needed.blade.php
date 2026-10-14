@extends('layouts.admin')

@section('title', 'Database Migration Required | ADS Sales Order System')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="glass-panel bg-white rounded-3xl shadow-sm border border-amber-200 p-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="space-y-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-200">
                    DATABASE SETUP REQUIRED
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900">Database Tables Not Yet Migrated</h1>
                <p class="text-sm text-slate-600 leading-relaxed">
                    The ADS Sales Order System requires the new database tables (<code class="bg-slate-100 px-1.5 py-0.5 rounded text-xs font-mono text-slate-800">sales_orders</code>, <code class="bg-slate-100 px-1.5 py-0.5 rounded text-xs font-mono text-slate-800">order_logs</code>, and workflow security roles).
                </p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-800">How to activate the ADS Sales Order System:</h2>
            <div class="p-4 rounded-2xl bg-slate-900 text-slate-100 font-mono text-xs space-y-2 shadow-inner">
                <p class="text-slate-400"># In your server terminal (SSH), run:</p>
                <p class="text-emerald-400 select-all font-bold">php artisan migrate --force</p>
                <p class="text-slate-400 mt-2"># Then clear system view and route cache:</p>
                <p class="text-emerald-400 select-all font-bold">php artisan optimize:clear</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('admin.sales-orders.index') }}" class="h-10 px-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh Page</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="h-10 px-4 inline-flex items-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
