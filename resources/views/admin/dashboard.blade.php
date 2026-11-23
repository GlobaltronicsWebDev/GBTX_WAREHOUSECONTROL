@extends('layouts.operations-dashboard')

@section('title', 'Operations Overview')

@section('content')
    @php
        $user = Auth::user();
        $firstName = explode(' ', trim($user->name), 2)[0] ?: $user->name;
        $greeting = now()->hour < 12 ? 'Good Day' : 'Good Afternoon';
        $canReviewSrf = $user->canManageSrf();
        $pendingApprovalCount = $user->canViewWarehouseOperations() || $user->isItAdmin()
            ? $srfPendingCount
            : $userSrfPendingCount;
        $visibleSrfCount = $user->canViewWarehouseOperations() || $user->isItAdmin()
            ? $srfTotalCount
            : $userSrfCount;
    @endphp

    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm bg-[#2563EB] text-white" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linejoin="round" d="M4 19V5m0 14h17"/><path stroke-linejoin="round" d="m7 15 4-4 3 2 6-7"/>
                    </svg>
                </span>
                <div>
                    <h1 id="dashboardGreeting" data-first-name="{{ $firstName }}" class="text-[20px] font-semibold leading-6 tracking-tight text-slate-900">{{ $greeting }} {{ $firstName }}!</h1>
                    <p class="mt-1 max-w-3xl text-[12px] leading-5 text-slate-500">Track, manage and keep your transactions organized.</p>
                </div>
            </div>
            <span class="inline-flex w-fit shrink-0 items-center border border-[#E5E7EB] bg-[#F8F9FA] px-2 py-1 text-[10px] font-medium uppercase tracking-wide text-slate-500">ERP preview · Connected records only</span>
        </section>

        <section class="-mx-3 flex flex-col gap-3 border-y border-[#E5E7EB] px-3 py-2.5 sm:-mx-5 sm:px-5 xl:flex-row xl:items-center xl:justify-between" aria-label="Operational actions and telemetry">
            <div class="flex shrink-0 items-center gap-2 text-[10px] uppercase tracking-wide text-slate-500">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="1"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                </svg>
                <time id="dashboardDate" class="whitespace-nowrap">Loading date</time>
                <span class="text-slate-300">|</span>
                <time id="dashboardClock" class="tabular-nums whitespace-nowrap font-mono text-[11px] font-medium text-slate-700">--:--:--</time>
            </div>

            <div class="flex min-w-0 items-center gap-2 overflow-x-auto pb-1 xl:justify-end xl:pb-0" aria-label="Quick actions">
                <a href="{{ route('admin.inventory.create') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-[#2563EB] hover:text-blue-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h4"/></svg>
                    <span>Item Entry</span>
                </a>
                <button type="button" disabled title="Prototype navigation only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v14m-5-5 5 5 5-5M4 20h16"/></svg>
                    <span>Stock In</span>
                </button>
                <button type="button" disabled title="Prototype navigation only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21V7m5 5-5-5-5 5M4 4h16"/></svg>
                    <span>Stock Out</span>
                </button>
                <button type="button" disabled title="Prototype navigation only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M3 8h18v12H3zM5 8l2-4h10l2 4M12 4v10m-4-4 4 4 4-4"/></svg>
                    <span>Import Entry</span>
                </button>
                <button type="button" disabled title="Prototype navigation only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M3 6h18v14H3zM7 6V4h10v2M7 10h10M7 14h6"/></svg>
                    <span>Projects</span>
                </button>
                <a href="{{ route('admin.srf.approved') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16.5 9"/></svg>
                    <span>Approvals</span>
                </a>
                <button type="button" onclick="openInstallationSrfModal()" @disabled(! $canReviewSrf) class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-[#2563EB] disabled:cursor-not-allowed disabled:text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                    <span>Requisitions</span>
                </button>
                <button type="button" disabled title="Prototype navigation only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M3 7h18v13H3zM7 7V4h10v3M7 12h10M7 16h6"/></svg>
                    <span>Purchases</span>
                </button>
                <button type="button" disabled title="Prototype navigation only" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1"/><path stroke-linecap="round" d="M3 10h18M7 15h4"/></svg>
                    <span>Payments</span>
                </button>
                <a href="{{ route('admin.reports.index') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linejoin="round" d="M4 19V5m0 14h17"/><path stroke-linejoin="round" d="m7 15 4-4 3 2 6-7"/></svg>
                    <span>Reports</span>
                </a>
                @if ($user->canAccessAdminCredentials())
                    <a href="{{ route('admin.settings.companies') }}" class="flex h-12 w-[58px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-600 hover:text-[#2563EB]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m19.4 15 .1.1 1 1.7-1.6 2.8-2-.5a8 8 0 0 1-1.5.9l-.4 2h-3.2l-.4-2a8 8 0 0 1-1.5-.9l-2 .5-1.6-2.8 1-1.7a8 8 0 0 1 0-1.8l-1-1.7 1.6-2.8 2 .5a8 8 0 0 1 1.5-.9l.4-2h3.2l.4 2a8 8 0 0 1 1.5.9l2-.5 1.6 2.8-1 1.7a8 8 0 0 1 0 1.8Z"/></svg>
                        <span>Settings</span>
                    </a>
                @else
                    <button type="button" disabled title="Settings are restricted to IT administrators" class="flex h-12 w-[58px] shrink-0 cursor-not-allowed flex-col items-center justify-center gap-1 text-[10px] leading-none text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M19.4 15 21 17.8l-1.6 2.8-2-.5a8 8 0 0 1-1.5.9l-.4 2h-3.2l-.4-2a8 8 0 0 1-1.5-.9l-2 .5-1.6-2.8L5.4 15a8 8 0 0 1 0-1.8l-1-1.7L6 8.7l2 .5a8 8 0 0 1 1.5-.9l.4-2h3.2l.4 2a8 8 0 0 1 1.5.9l2-.5 1.6 2.8-1 1.7a8 8 0 0 1 0 1.8Z"/></svg>
                        <span>Settings</span>
                    </button>
                @endif

                <div class="ml-auto inline-flex shrink-0 items-center border border-[#E5E7EB] text-[11px]" role="group" aria-label="Dashboard side panel">
                    <button type="button" data-dashboard-tab="alerts" aria-pressed="true" class="bg-[#EAECEF] px-2.5 py-2 font-medium text-slate-900">Alerts</button>
                    <button type="button" data-dashboard-tab="stats" aria-pressed="false" class="px-2.5 py-2 font-medium text-slate-500 hover:bg-[#F8F9FA]">Stats</button>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-2.5 lg:grid-cols-4" aria-label="Operational metrics">
            <article class="min-w-0 border border-[#E5E7EB] bg-white px-3 py-2.5">
                <p class="text-[11px] font-medium text-slate-500">Pending Approvals</p>
                <div class="mt-1 flex items-end justify-between gap-2">
                    <p class="tabular-nums text-[22px] font-semibold leading-6">{{ number_format($pendingApprovalCount) }}</p>
                    <span class="text-[10px] text-slate-400">Live SRFs</span>
                </div>
                <p class="mt-1 truncate text-[10px] text-slate-500">Awaiting a warehouse decision</p>
            </article>
            <article class="min-w-0 border border-[#E5E7EB] bg-white px-3 py-2.5">
                <p class="text-[11px] font-medium text-slate-500">Low Stock SKUs</p>
                <div class="mt-1 flex items-end justify-between gap-2">
                    <p class="tabular-nums text-[22px] font-semibold leading-6">—</p>
                    <span class="text-[10px] text-slate-400">Unavailable</span>
                </div>
                <p class="mt-1 truncate text-[10px] text-slate-500">Inventory data is not tracked</p>
            </article>
            <article class="min-w-0 border border-[#E5E7EB] bg-white px-3 py-2.5">
                <p class="text-[11px] font-medium text-slate-500">Active Import Shipments</p>
                <div class="mt-1 flex items-end justify-between gap-2">
                    <p class="tabular-nums text-[22px] font-semibold leading-6">—</p>
                    <span class="text-[10px] text-slate-400">Unavailable</span>
                </div>
                <p class="mt-1 truncate text-[10px] text-slate-500">Shipment data is not tracked</p>
            </article>
            <article class="min-w-0 border border-[#E5E7EB] bg-white px-3 py-2.5">
                <p class="text-[11px] font-medium text-slate-500">Today’s Dispatches</p>
                <div class="mt-1 flex items-end justify-between gap-2">
                    <p class="tabular-nums text-[22px] font-semibold leading-6">—</p>
                    <span class="text-[10px] text-slate-400">Unavailable</span>
                </div>
                <p class="mt-1 truncate text-[10px] text-slate-500">Dispatch data is not tracked</p>
            </article>
        </section>

        <section class="grid min-w-0 gap-3 xl:grid-cols-[minmax(0,7fr)_minmax(280px,3fr)]">
            <section class="min-w-0 border border-[#E5E7EB] bg-white" aria-labelledby="transactionsHeading">
                <div class="flex flex-col gap-1 border-b border-[#E5E7EB] px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 id="transactionsHeading" class="text-[13px] font-semibold text-slate-800">Recent SRF Transactions</h2>
                        <p class="text-[10px] text-slate-500">Showing current Stock Requisition Forms from the application database.</p>
                    </div>
                    <span class="w-fit border border-[#E5E7EB] px-1.5 py-0.5 text-[9px] uppercase tracking-wide text-slate-500">Live SRF records</span>
                </div>

                <div class="overflow-x-auto">
                    <table id="transactionsTable" class="w-full min-w-[890px] border-collapse text-left text-[11px]">
                        <thead class="h-8 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium text-slate-500">
                            <tr>
                                <th class="w-8 px-3"><input type="checkbox" aria-label="Select all rows" class="h-3.5 w-3.5 accent-[#2563EB]"></th>
                                <th class="px-2"><button type="button" data-sort-key="sortRef" class="flex items-center gap-1">Ref ID <span aria-hidden="true">↕</span></button></th>
                                <th class="px-2"><button type="button" data-sort-key="sortModule" class="flex items-center gap-1">Module <span aria-hidden="true">↕</span></button></th>
                                <th class="px-2"><button type="button" data-sort-key="sortProject" class="flex items-center gap-1">Project / BL <span aria-hidden="true">↕</span></button></th>
                                <th class="px-2"><button type="button" data-sort-key="sortItem" class="flex items-center gap-1">Item / Description <span aria-hidden="true">↕</span></button></th>
                                <th class="px-2 text-right"><button type="button" data-sort-key="sortQty" class="ml-auto flex items-center gap-1">Qty <span aria-hidden="true">↕</span></button></th>
                                <th class="px-2"><button type="button" data-sort-key="sortStatus" class="flex items-center gap-1">Status <span aria-hidden="true">↕</span></button></th>
                                <th class="px-3"><button type="button" data-sort-key="sortTimestamp" class="flex items-center gap-1">Timestamp <span aria-hidden="true">↕</span></button></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @forelse ($groupedSrfRequisitions->take(4) as $srfNumber => $items)
                                @php
                                    $first = $items->first();
                                    $statusLabel = match ($first->status) {
                                        'approved', 'completed', 'verified' => 'Approved',
                                        'on_hold' => 'On Hold',
                                        'declined', 'rejected' => 'Declined',
                                        default => 'Pending',
                                    };
                                    $timestamp = $first->created_at?->format('Y-m-d H:i:s') ?? '';
                                @endphp
                                <tr class="h-9 text-slate-700" data-sort-ref="{{ $srfNumber }}" data-sort-module="SRF" data-sort-project="{{ $first->project_name }}" data-sort-item="{{ $first->item_model }}" data-sort-qty="{{ (int) $first->quantity }}" data-sort-status="{{ $statusLabel }}" data-sort-timestamp="{{ $timestamp }}">
                                    <td class="px-3"><input type="checkbox" aria-label="Select SRF {{ $srfNumber }}" class="h-3.5 w-3.5 accent-[#2563EB]"></td>
                                    <td class="whitespace-nowrap px-2 font-mono text-[10px] tabular-nums">{{ $srfNumber }}</td>
                                    <td class="px-2">SRF <span class="border border-[#E5E7EB] px-1 text-[9px] text-slate-500">LIVE</span></td>
                                    <td class="max-w-32 truncate px-2">{{ $first->client ?: $first->project_name }}</td>
                                    <td class="max-w-48 truncate px-2">{{ $first->item_model }}@if ($items->count() > 1)<span class="text-slate-400"> +{{ $items->count() - 1 }} more</span>@endif</td>
                                    <td class="whitespace-nowrap px-2 text-right tabular-nums">{{ number_format($first->quantity) }} {{ $first->uom ?: 'PCS' }}</td>
                                    <td class="px-2"><span class="whitespace-nowrap border border-[#E5E7EB] bg-[#F8F9FA] px-1.5 py-0.5 text-[10px]">{{ $statusLabel }}</span></td>
                                    <td class="whitespace-nowrap px-3 font-mono text-[10px] tabular-nums">{{ $first->created_at?->format('M d, H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-6 text-center text-[11px] text-slate-500">No live SRF records are available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[#E5E7EB] px-3 py-2 text-[10px] text-slate-400">SRF records and notifications are loaded from the application database. Inventory, shipment, procurement, and dispatch data are not tracked.</div>
            </section>

            <aside class="min-w-0 border border-[#E5E7EB] bg-white" aria-labelledby="queueHeading">
                <div class="flex items-start justify-between gap-2 border-b border-[#E5E7EB] px-3 py-2">
                    <div>
                        <h2 id="queueHeading" class="text-[13px] font-semibold text-slate-800">System Alerts &amp; Action Queue</h2>
                        <p class="text-[10px] text-slate-500">Notifications and pending SRF reviews</p>
                    </div>
                    @if (!empty($unreadNotificationCount) && $unreadNotificationCount > 0)
                        <div class="flex items-center gap-2">
                            <span class="whitespace-nowrap text-[9px] text-slate-500">{{ number_format($unreadNotificationCount) }} unread</span>
                            <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                                @csrf
                                <button type="submit" class="whitespace-nowrap text-[10px] text-slate-500 underline decoration-[#E5E7EB] underline-offset-2 hover:text-[#2563EB]">Mark all read</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div id="alertsPanel" data-dashboard-panel="alerts" class="divide-y divide-[#E5E7EB]">
                    @forelse (($headerNotifications ?? collect())->take(3) as $notification)
                        <article class="px-3 py-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[11px] leading-4 text-slate-700">{{ $notification->title }}</p>
                                <time class="shrink-0 font-mono text-[9px] tabular-nums text-slate-400">{{ $notification->created_at?->format('H:i') }}</time>
                            </div>
                            <p class="mt-1 text-[10px] leading-4 text-slate-500">{{ $notification->message }}</p>
                            @if (!$notification->is_read)
                                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}" class="mt-1.5">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-slate-500 underline decoration-[#E5E7EB] underline-offset-2 hover:text-[#2563EB]">Mark read</button>
                                </form>
                            @endif
                        </article>
                    @empty
                    @endforelse

                    @php
                        $pendingSrfAlerts = $srfRequisitions->whereIn('status', ['pending', 'for_approval'])->unique('srf_number')->take(2);
                    @endphp
                    @if (($headerNotifications ?? collect())->isEmpty() && $pendingSrfAlerts->isEmpty())
                        <p class="px-3 py-6 text-center text-[11px] text-slate-500">No live alerts or pending SRF reviews.</p>
                    @endif
                    @foreach ($pendingSrfAlerts as $pendingSrf)
                        <article class="px-3 py-2.5">
                            <p class="text-[11px] leading-4 text-slate-700">{{ $pendingSrf->srf_number }} awaiting warehouse review</p>
                            <p class="mt-1 text-[10px] text-slate-500">{{ $pendingSrf->project_name }} · {{ $pendingSrf->item_model }}</p>
                            <span class="mt-1 inline-block border border-[#E5E7EB] px-1 py-0.5 text-[9px] uppercase text-slate-500">Live SRF</span>
                        </article>
                    @endforeach

                </div>

                <div data-dashboard-panel="stats" hidden class="divide-y divide-[#E5E7EB]" aria-label="Live application statistics">
                    <div class="flex items-center gap-3 px-3 py-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="4" width="14" height="17" rx="2"/><path stroke-linecap="round" d="M9 4.5h6M8 10h8M8 14h8M8 18h5"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Requisitions</p>
                            <p class="font-mono text-[15px] font-semibold leading-5 tabular-nums">{{ number_format($visibleSrfCount) }}</p>
                            <p class="text-[9px] text-slate-400">Live SRF records</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-3 py-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17h8.9a2 2 0 0 0 1.9-1.4L22 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Purchase Requests</p>
                            <p class="font-mono text-[15px] font-semibold leading-5 tabular-nums">—</p>
                            <p class="text-[9px] text-slate-400">Not tracked</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-3 py-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange-50 text-orange-600" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h12v11H3zM15 11h4l3 3v4h-7z"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="18.5" cy="18.5" r="1.5"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Receivings</p>
                            <p class="font-mono text-[15px] font-semibold leading-5 tabular-nums">—</p>
                            <p class="text-[9px] text-slate-400">Not tracked</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-3 py-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Deliverables</p>
                            <p class="font-mono text-[15px] font-semibold leading-5 tabular-nums">—</p>
                            <p class="text-[9px] text-slate-400">Not tracked</p>
                        </div>
                    </div>
                    <p class="px-3 py-2 text-[10px] text-slate-400">Only SRF records are available as live requisition data.</p>
                </div>
            </aside>
        </section>
    </div>
@endsection

@section('modals')
    @if ($canReviewSrf)
        <div id="installationSrfModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="srfModalTitle">
            <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto border border-[#E5E7EB] bg-white">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3">
                    <div>
                        <h2 id="srfModalTitle" class="text-[16px] font-semibold text-slate-900">Create Stock Requisition Form</h2>
                        <p class="mt-1 text-[11px] text-slate-500">Item details are saved with the SRF; inventory quantities are not tracked.</p>
                    </div>
                    <button type="button" onclick="closeInstallationSrfModal()" class="px-2 py-1 text-[18px] leading-none text-slate-500 hover:bg-[#F8F9FA]" aria-label="Close">×</button>
                </div>
                <form method="POST" action="{{ route('admin.operations.installation.srf') }}" class="space-y-4 p-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <label class="text-[11px] font-medium text-slate-600">Project / client
                            <input name="project_name" value="{{ old('project_name') }}" class="mt-1 h-8 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]" required>
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">Client
                            <input name="client" value="{{ old('client') }}" class="mt-1 h-8 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">PO number
                            <input name="po_number" value="{{ old('po_number') }}" class="tabular-nums mt-1 h-8 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">Date needed
                            <input type="date" name="date_needed" value="{{ old('date_needed') }}" class="tabular-nums mt-1 h-8 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">Department
                            <input name="department" value="{{ old('department', Auth::user()->isTechnical() ? 'TECHNICAL' : 'WAREHOUSE') }}" class="mt-1 h-8 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">Prepared by
                            <input name="prepared_by" value="{{ old('prepared_by', Auth::user()->name) }}" class="mt-1 h-8 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]" required>
                        </label>
                    </div>
                    <input type="hidden" name="srf_number" value="{{ $autoSrfNumber }}">
                    <input type="hidden" name="sso_number" value="{{ $autoSsoNumber }}">
                    <div>
                        <div class="mb-2 flex items-center justify-between border-b border-[#E5E7EB] pb-2">
                            <h3 class="text-[12px] font-semibold text-slate-800">Requested items</h3>
                            <button type="button" onclick="addSrfItem()" class="border border-[#E5E7EB] px-2 py-1 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Add item</button>
                        </div>
                        <div id="srfItems" class="space-y-2">
                            <div class="grid gap-2 border border-[#E5E7EB] p-2 sm:grid-cols-2 lg:grid-cols-6">
                                <input name="items[0][item_model]" placeholder="Model / item name" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB] lg:col-span-2" required>
                                <input name="items[0][item_manufacturer]" placeholder="Manufacturer" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                                <input name="items[0][item_category]" placeholder="Category" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                                <input name="items[0][quantity]" type="number" min="1" value="1" placeholder="Qty" class="tabular-nums h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]" required>
                                <input name="items[0][uom]" value="PCS" placeholder="UOM" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                                <textarea name="items[0][item_description]" placeholder="Item description" class="min-h-8 border border-[#E5E7EB] px-2 py-1 text-[12px] outline-none focus:border-[#2563EB] sm:col-span-2 lg:col-span-3"></textarea>
                                <input name="items[0][remarks]" placeholder="Remarks" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB] sm:col-span-2 lg:col-span-3">
                            </div>
                        </div>
                    </div>
                    <label class="block text-[11px] font-medium text-slate-600">Notes
                        <textarea name="notes" rows="2" class="mt-1 w-full border border-[#E5E7EB] px-2 py-1 text-[12px] outline-none focus:border-[#2563EB]">{{ old('notes') }}</textarea>
                    </label>
                    <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3">
                        <button type="button" onclick="closeInstallationSrfModal()" class="h-8 border border-[#E5E7EB] px-3 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">Cancel</button>
                        <button type="submit" class="h-8 bg-[#2563EB] px-3 text-[12px] font-medium text-white hover:bg-blue-700">Submit SRF</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            let srfItemIndex = 1;

            function openInstallationSrfModal() {
                document.getElementById('installationSrfModal')?.classList.replace('hidden', 'flex');
            }

            function closeInstallationSrfModal() {
                const modal = document.getElementById('installationSrfModal');
                modal?.classList.add('hidden');
                modal?.classList.remove('flex');
            }

            function addSrfItem() {
                const index = srfItemIndex++;
                const row = document.createElement('div');
                row.className = 'grid gap-2 border border-[#E5E7EB] p-2 sm:grid-cols-2 lg:grid-cols-6';
                row.innerHTML = `
                    <input name="items[${index}][item_model]" placeholder="Model / item name" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB] lg:col-span-2" required>
                    <input name="items[${index}][item_manufacturer]" placeholder="Manufacturer" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    <input name="items[${index}][item_category]" placeholder="Category" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    <input name="items[${index}][quantity]" type="number" min="1" value="1" placeholder="Qty" class="tabular-nums h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]" required>
                    <input name="items[${index}][uom]" value="PCS" placeholder="UOM" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    <textarea name="items[${index}][item_description]" placeholder="Item description" class="min-h-8 border border-[#E5E7EB] px-2 py-1 text-[12px] outline-none focus:border-[#2563EB] sm:col-span-2 lg:col-span-3"></textarea>
                    <input name="items[${index}][remarks]" placeholder="Remarks" class="h-8 border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB] sm:col-span-2 lg:col-span-3">
                    <button type="button" onclick="this.parentElement.remove()" class="text-left text-[11px] text-slate-500 underline lg:col-span-6">Remove item</button>
                `;
                document.getElementById('srfItems')?.appendChild(row);
            }
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-sort-key]').forEach((button) => {
            button.addEventListener('click', () => {
                const tableBody = document.querySelector('#transactionsTable tbody');
                const key = button.dataset.sortKey;
                const ascending = button.dataset.sortDirection !== 'ascending';
                const rows = [...tableBody.querySelectorAll('tr')];

                rows.sort((a, b) => {
                    const left = a.dataset[key] ?? '';
                    const right = b.dataset[key] ?? '';
                    const comparison = key === 'sortQty'
                        ? Number(left) - Number(right)
                        : left.localeCompare(right, undefined, { numeric: true, sensitivity: 'base' });

                    return ascending ? comparison : -comparison;
                });

                rows.forEach((row) => tableBody.appendChild(row));
                document.querySelectorAll('[data-sort-key]').forEach((header) => {
                    header.dataset.sortDirection = '';
                    header.setAttribute('aria-sort', 'none');
                });
                button.dataset.sortDirection = ascending ? 'ascending' : 'descending';
                button.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
            });
        });
    </script>
@endpush
