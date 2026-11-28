@extends('layouts.operations-dashboard')

@section('title', 'Inventory Management')
@section('breadcrumb', 'Inventory / Dashboard')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm bg-[#2563EB] text-white" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5 12 3l8 2.5v13L12 21l-8-2.5v-13Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 5.5 8 2.7 8-2.7M12 8.2V21"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-[20px] font-semibold leading-6 tracking-tight text-slate-900">Inventory Management</h1>
                    <p class="mt-1 max-w-3xl text-[12px] leading-5 text-slate-500">Stock movement, item activity, and stock health overview.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-start gap-2">
                <a href="{{ route('admin.inventory.export') }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Export CSV</a>
                @if (Auth::user()->isAdmin())
                    <details class="relative">
                        <summary class="cursor-pointer rounded-sm bg-[#2563EB] px-3 py-2 text-[11px] font-medium text-white">Import CSV</summary>
                        <form method="POST" action="{{ route('admin.inventory.import') }}" enctype="multipart/form-data" class="absolute right-0 z-10 mt-2 w-72 space-y-3 border border-[#E5E7EB] bg-white p-3 shadow-lg">
                            @csrf
                            <label for="csv_file" class="block text-[11px] font-medium text-slate-700">Inventory CSV file</label>
                            <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" required class="block w-full text-[11px] text-slate-700">
                            <p class="text-[10px] text-slate-500">Use the exported CSV as a template. Existing IDs update items; leave ID blank to add. Fill all quantity and SQM fields (use 0 if needed). Histories are replaced. Maximum 5 MB and 5,000 records.</p>
                            <p class="text-[10px] text-slate-500">The centralized LED inventory spreadsheet is also supported. Each upload creates new items; importing it again creates duplicates. Blank quantities become zero, negative availability becomes zero, and per-panel SQM is rounded to two decimals. Multiple received dates are preserved in history.</p>
                            <p class="text-[10px] text-slate-500">Enter the first history under History Qty and Project. Additional Histories is a JSON list, for example: <code>[{"quantity":2,"project":"Client"}]</code>. Use [] for none.</p>
                            <p class="text-[10px] text-slate-500">Keep both header rows. Location must match the Warehouse ID; use the ID below to select a warehouse.</p>
                            <p class="text-[10px] text-slate-500">Active warehouse IDs:
                                @forelse ($warehouseLocations as $warehouseLocation)
                                    {{ $warehouseLocation->id }}: {{ $warehouseLocation->name }}{{ $loop->last ? '' : ',' }}
                                @empty
                                    None available.
                                @endforelse
                            </p>
                            <button type="submit" class="rounded-sm bg-[#2563EB] px-3 py-2 text-[11px] text-white">Upload CSV</button>
                        </form>
                    </details>
                @endif
            </div>
        </section>

        @if (session('status'))
            <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="-mx-3 flex flex-col gap-3 border-y border-[#E5E7EB] px-3 py-2.5 sm:-mx-5 sm:px-5 xl:flex-row xl:items-center xl:justify-between" aria-label="Inventory actions and telemetry">
            <div class="flex shrink-0 items-center gap-2 text-[10px] uppercase tracking-wide text-slate-500">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="1"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                </svg>
                <time id="dashboardDate" class="whitespace-nowrap">Loading date</time>
                <span class="text-slate-300">|</span>
                <time id="dashboardClock" class="tabular-nums whitespace-nowrap font-mono text-[11px] font-medium text-slate-700">--:--:--</time>
            </div>

            <div class="flex min-w-0 items-center gap-2 overflow-x-auto pb-1 xl:justify-end xl:pb-0" aria-label="Inventory quick actions">
                <a href="{{ route('admin.inventory.create') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h4"/></svg>
                    <span>Item Entry</span>
                </a>
                <a href="{{ route('admin.stock-in.index') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v14m-5-5 5 5 5-5M4 20h16"/></svg>
                    <span>Stock In</span>
                </a>
                <a href="{{ route('admin.stock-out.index') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21V7m5 5-5-5-5 5M4 4h16"/></svg>
                    <span>Stock Out</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M4 19V5m0 14h17"/><path stroke-linejoin="round" d="m7 15 4-4 3 2 6-7"/></svg>
                    <span>Reports</span>
                </a>
                <button type="button" disabled title="Inventory actions are prototype only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M3 6h18v14H3zM7 6V4h10v2M7 10h10M7 14h6"/></svg>
                    <span>Projects</span>
                </button>
                <a href="{{ route('admin.srf.approved') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16.5 9"/></svg>
                    <span>Approvals</span>
                </a>
                <button type="button" disabled title="Inventory actions are prototype only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14v14H5zM8 9h8M8 12h8M8 15h5"/></svg>
                    <span>Requisitions</span>
                </button>
                <button type="button" disabled title="Inventory actions are prototype only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M3 7h18v13H3zM7 7V4h10v3M7 12h10M7 16h6"/></svg>
                    <span>Purchases</span>
                </button>
                <button type="button" disabled title="Inventory actions are prototype only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1"/><path stroke-linecap="round" d="M3 10h18M7 15h4"/></svg>
                    <span>Payments</span>
                </button>
                @if (Auth::user()->canAccessAdminCredentials())
                    <a href="{{ route('admin.settings.companies') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m19.4 15 .1.1 1 1.7-1.6 2.8-2-.5a8 8 0 0 1-1.5.9l-.4 2h-3.2l-.4-2a8 8 0 0 1-1.5-.9l-2-.5-1.6-2.8 1.6-2.8a8 8 0 0 1 0-1.8L5.4 8.7 7 5.9l2 .5a8 8 0 0 1 1.5-.9l.4-2h3.2l.4 2a8 8 0 0 1 1.5.9l2-.5 1.6 2.8-1 1.7a8 8 0 0 1 0 1.8Z"/></svg>
                        <span>Settings</span>
                    </a>
                @else
                    <button type="button" disabled title="Settings are restricted to IT administrators" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m19.4 15 .1.1 1 1.7-1.6 2.8-2-.5a8 8 0 0 1-1.5.9l-.4 2h-3.2l-.4-2a8 8 0 0 1-1.5-.9l-2-.5-1.6-2.8 1.6-2.8a8 8 0 0 1 0-1.8L5.4 8.7 7 5.9l2 .5a8 8 0 0 1 1.5-.9l.4-2h3.2l.4 2a8 8 0 0 1 1.5.9l2-.5 1.6 2.8-1 1.7a8 8 0 0 1 0 1.8Z"/></svg>
                        <span>Settings</span>
                    </button>
                @endif
            </div>
        </section>

        <section class="grid min-w-0 gap-3 xl:grid-cols-[minmax(0,3fr)_minmax(300px,2fr)]">
            <div class="min-w-0 space-y-3">
                <section class="grid grid-cols-1 gap-3 sm:grid-cols-2" aria-label="Inventory metrics">
                    <article class="flex min-h-20 items-center gap-3 border border-[#E5E7EB] bg-white px-4 py-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-blue-100 bg-blue-50 text-blue-600" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/></svg>
                        </span>
                        <div>
                            <p class="text-[11px] text-slate-500">Today’s Movement</p>
                            <p class="mt-0.5 font-mono text-[22px] font-semibold leading-6 tabular-nums">0</p>
                        </div>
                    </article>
                    <article class="flex min-h-20 items-center gap-3 border border-[#E5E7EB] bg-white px-4 py-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 text-emerald-600" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17 9 11l4 4 8-9"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 6h6v6"/></svg>
                        </span>
                        <div>
                            <p class="text-[11px] text-slate-500">Daily Average (7 Days)</p>
                            <p class="mt-0.5 font-mono text-[22px] font-semibold leading-6 tabular-nums">0</p>
                        </div>
                    </article>
                </section>

                <section class="border border-[#E5E7EB] bg-white" aria-labelledby="activityHeading">
                    <div class="flex items-center justify-between border-b border-[#E5E7EB] px-3 py-2.5">
                        <h2 id="activityHeading" class="text-[12px] font-semibold text-slate-800">Last 7 Days Activity</h2>
                        <span class="text-[10px] uppercase tracking-wide text-slate-400">Sample</span>
                    </div>
                    <div class="px-3 pt-3">
                        <div class="grid h-24 grid-cols-7 border-b border-[#E5E7EB]" aria-label="Zero movement recorded for the last seven days">
                            @foreach (range(6, 0) as $daysAgo)
                                <div class="flex items-end justify-center pb-2 text-[10px] font-mono tabular-nums text-slate-400">{{ now()->subDays($daysAgo)->format('M d') }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-[#E5E7EB] py-2 sm:grid-cols-5 lg:grid-cols-9">
                            @foreach ([
                                'DO' => 'text-blue-600',
                                'EQU' => 'text-green-600',
                                'GEN' => 'text-slate-500',
                                'OS' => 'text-violet-600',
                                'PM' => 'text-orange-500',
                                'PPE' => 'text-sky-600',
                                'SCR' => 'text-blue-500',
                                'SP' => 'text-emerald-600',
                                'TOO' => 'text-amber-600',
                            ] as $category => $iconColor)
                                <div class="flex min-w-0 items-center justify-center gap-1.5 px-1 py-1 text-[10px] text-slate-600">
                                    <svg class="h-3.5 w-3.5 shrink-0 {{ $iconColor }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linejoin="round" d="m12 3 9 5-9 5-9-5 9-5Z"/><path stroke-linejoin="round" d="M3 12l9 5 9-5M3 16l9 5 9-5"/></svg>
                                    <span class="truncate">{{ $category }}</span>
                                    <span class="font-mono tabular-nums text-slate-900">0</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="min-h-48 border border-[#E5E7EB] bg-white" aria-labelledby="movementHeading">
                    <div class="border-b border-[#E5E7EB] px-3 py-2.5">
                        <h2 id="movementHeading" class="text-[12px] font-semibold text-slate-800">Recent Movement</h2>
                    </div>
                    <div class="flex min-h-40 flex-col items-center justify-center gap-2 px-4 py-6 text-center">
                        <svg class="h-9 w-9 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="m5.5 5.5 13 13"/>
                        </svg>
                        <p class="text-[12px] font-medium text-slate-700">No movement recorded</p>
                        <p class="text-[10px] text-slate-400">Inventory transactions are not connected in this prototype.</p>
                    </div>
                </section>
            </div>

            <section class="min-w-0 border border-[#E5E7EB] bg-white" aria-labelledby="stockHealthHeading">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-3 py-2.5">
                    <h2 id="stockHealthHeading" class="text-[12px] font-semibold text-slate-800">Stock Health</h2>
                    <a href="{{ route('admin.inventory.dashboard') }}#stockHealthHeading" class="border border-[#E5E7EB] px-2 py-1 text-[10px] text-slate-600 hover:text-[#2563EB]">Refresh</a>
                </div>
                <div class="flex flex-col gap-5 p-4 sm:flex-row sm:items-center sm:justify-around xl:flex-col xl:items-stretch 2xl:flex-row 2xl:items-center">
                    <div class="flex shrink-0 justify-center">
                        <div class="flex h-32 w-32 items-center justify-center rounded-full border-[10px] border-[#F1F3F5]">
                            <div class="text-center">
                                <p class="font-mono text-[20px] font-semibold leading-6 tabular-nums">{{ $totalItems }}</p>
                                <p class="mt-1 text-[9px] uppercase tracking-wide text-slate-400">Total Items</p>
                            </div>
                        </div>
                    </div>
                    <dl class="min-w-0 flex-1 space-y-3">
                        @foreach ([
                            'In Stock' => 'bg-emerald-500',
                            'Low Stock' => 'bg-amber-500',
                            'Last Stock' => 'bg-orange-500',
                            'Out of Stock' => 'bg-red-500',
                        ] as $status => $indicatorColor)
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $indicatorColor }}" aria-hidden="true"></span>
                                <dt class="min-w-0 flex-1 truncate text-slate-600">{{ $status }}</dt>
                                <dd class="w-6 text-right font-mono font-medium tabular-nums text-slate-800">{{ $stockHealth[$status] }}</dd>
                                <dd class="w-9 text-right font-mono tabular-nums text-slate-500">{{ $totalItems > 0 ? round($stockHealth[$status] / $totalItems * 100, 1) : 0 }}%</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
                <p class="border-t border-[#E5E7EB] px-3 py-2 text-[10px] text-slate-500">Based on available quantity: In Stock 6+, Low Stock 2–5, Last Stock 1, Out of Stock 0.</p>
            </section>
        </section>
    </div>
@endsection
