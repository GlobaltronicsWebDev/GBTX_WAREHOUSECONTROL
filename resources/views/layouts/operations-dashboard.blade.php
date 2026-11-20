<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-white text-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Operations Overview') | Globaltronics</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/globaltronics_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        body {
            font-family: Poppins, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        @media (min-width: 1024px) {
            #dashboardSidebar.is-collapsed {
                width: 72px;
            }

            #dashboardSidebar.is-collapsed .sidebar-copy,
            #dashboardSidebar.is-collapsed .sidebar-section-label,
            #dashboardSidebar.is-collapsed .sidebar-subnav,
            #dashboardSidebar.is-collapsed .sidebar-bottom-label {
                display: none;
            }

            #dashboardSidebar.is-collapsed .sidebar-nav-button {
                justify-content: center;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    <div id="dashboardSidebarBackdrop" class="fixed inset-0 z-30 hidden bg-slate-950/25 lg:hidden" aria-hidden="true"></div>

    <div class="flex min-h-screen">
        <aside id="dashboardSidebar" class="fixed inset-y-0 left-0 z-40 flex w-[240px] -translate-x-full flex-col border-r border-[#E5E7EB] bg-[#F8F9FA] transition-[width,transform] duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex h-12 shrink-0 items-center gap-2.5 border-b border-[#E5E7EB] px-4">
                <img
                    src="{{ asset('assets/images/logo/GBTX_ROT.gif') }}"
                    alt="Globaltronics logo"
                    class="h-auto w-12 shrink-0 object-contain"
                    width="48"
                    height="27"
                >
                <span class="sidebar-copy text-[14px] font-semibold tracking-tight">Globaltronics IMS</span>
                <button type="button" id="sidebarClose" class="ml-auto rounded-sm p-1 text-slate-500 hover:bg-[#EAECEF] lg:hidden" aria-label="Close navigation">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>

            <nav class="min-h-0 flex-1 overflow-y-auto px-2 py-3 text-[13px]" aria-label="Primary navigation">
                <p class="sidebar-section-label px-2 pb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Workspace</p>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-button mb-1 flex h-8 items-center gap-2.5 rounded-sm px-2 {{ request()->routeIs('admin.dashboard') ? 'bg-[#EAECEF] text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <span class="sidebar-copy">My Dashboard</span>
                </a>

                <p class="sidebar-section-label px-2 pb-1 pt-3 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Operations</p>
                <div class="space-y-0.5">
                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="inventoryMenu" aria-expanded="{{ request()->routeIs('admin.inventory.dashboard') ? 'true' : 'false' }}" aria-controls="inventoryMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linejoin="round" d="m3 7 9-4 9 4-9 4-9-4Z"/><path stroke-linejoin="round" d="M3 7v10l9 4 9-4V7M12 11v10"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Inventory</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
                        </svg>
                    </button>
                    <div id="inventoryMenu" class="sidebar-subnav pb-1 pl-8 {{ request()->routeIs('admin.inventory.dashboard') ? '' : 'hidden' }}">
                        <a href="{{ route('admin.inventory.dashboard') }}" class="block h-7 w-full text-left text-[12px] {{ request()->routeIs('admin.inventory.dashboard') ? 'font-medium text-[#2563EB]' : 'text-slate-500 hover:text-slate-700' }}" @if (request()->routeIs('admin.inventory.dashboard')) aria-current="page" @endif>Dashboard</a>
                        <button type="button" disabled title="Prototype navigation only" class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Inventory List</button>
                    </div>

                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="warehouseMenu" aria-expanded="{{ request()->routeIs('admin.warehouses.*') ? 'true' : 'false' }}" aria-controls="warehouseMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6M9 10h.01M15 10h.01"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Warehouse</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/></svg>
                    </button>
                    <div id="warehouseMenu" class="sidebar-subnav pb-1 pl-8 {{ request()->routeIs('admin.warehouses.*') ? '' : 'hidden' }}">
                        @forelse ($warehouseNavigationLocations as $warehouseNavigationLocation)
                            <a
                                href="{{ route('admin.warehouses.show', $warehouseNavigationLocation) }}"
                                @if (request()->routeIs('admin.warehouses.show') && request()->route('location')?->is($warehouseNavigationLocation)) aria-current="page" @endif
                                class="block h-7 w-full truncate text-left text-[12px] {{ request()->routeIs('admin.warehouses.show') && request()->route('location')?->is($warehouseNavigationLocation) ? 'font-medium text-[#2563EB]' : 'text-[#7891B8] hover:text-[#2563EB]' }}"
                            >{{ $warehouseNavigationLocation->name }}</a>
                        @empty
                            <span class="block py-1 text-[11px] text-slate-400">No locations configured</span>
                        @endforelse
                    </div>

                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="projectMenu" aria-expanded="false" aria-controls="projectMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linejoin="round" d="M3 6.5h7l2 2H21v10.8a1.7 1.7 0 0 1-1.7 1.7H4.7A1.7 1.7 0 0 1 3 19.3V6.5Z"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Project</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/></svg>
                    </button>
                    <div id="projectMenu" class="sidebar-subnav hidden pb-1 pl-8">
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left font-mono text-[11px] text-slate-400">9FE-4479</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left font-mono text-[11px] text-slate-400">SXN-2816</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Project Lists</button>
                    </div>

                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="logisticsMenu" aria-expanded="false" aria-controls="logisticsMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v11H3zM14 11h4l3 3v4h-7z"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="18" cy="18" r="1.5"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Logistics</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/></svg>
                    </button>
                    <div id="logisticsMenu" class="sidebar-subnav hidden pb-1 pl-8">
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Receiving</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Delivery</button>
                    </div>

                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="importMenu" aria-expanded="false" aria-controls="importMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linejoin="round" d="M3 8h18v12H3zM5 8l2-4h10l2 4M12 4v10m-4-4 4 4 4-4"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Importation</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/></svg>
                    </button>
                    <div id="importMenu" class="sidebar-subnav hidden pb-1 pl-8">
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Shipments &amp; BL</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Customs Clearance</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Landed Costing</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Brokerage &amp; Duties</button>
                    </div>

                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="procurementMenu" aria-expanded="false" aria-controls="procurementMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17h8.9a2 2 0 0 0 1.9-1.4L22 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Procurement</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/></svg>
                    </button>
                    <div id="procurementMenu" class="sidebar-subnav hidden pb-1 pl-8">
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Requisitions</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Purchase Orders</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Purchase Requests</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Payment Requests</button>
                    </div>

                    <a href="{{ route('admin.srf.approved') }}" class="sidebar-nav-button flex h-8 items-center gap-2.5 rounded-sm px-2 text-slate-700 hover:bg-[#EAECEF]">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16.5 9"/>
                        </svg>
                        <span class="sidebar-copy">Approvals</span>
                    </a>

                    <button type="button" class="sidebar-nav-button flex h-8 w-full items-center gap-2.5 rounded-sm px-2 text-left text-slate-700 hover:bg-[#EAECEF]" data-menu-toggle="masterDataMenu" aria-expanded="false" aria-controls="masterDataMenu">
                        <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <ellipse cx="12" cy="5" rx="8" ry="3"/><path stroke-linecap="round" d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>
                        </svg>
                        <span class="sidebar-copy flex-1">Master Data</span>
                        <svg class="sidebar-copy h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/></svg>
                    </button>
                    <div id="masterDataMenu" class="sidebar-subnav hidden pb-1 pl-8">
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Items</button>
                        <button type="button" disabled class="block h-7 w-full cursor-not-allowed text-left text-[12px] text-slate-400">Item Override</button>
                    </div>
                </div>
            </nav>

            <div class="shrink-0 border-t border-[#E5E7EB] p-2">
                <a href="{{ route('admin.reports.index') }}" class="sidebar-nav-button flex h-8 items-center gap-2.5 rounded-sm px-2 {{ request()->routeIs('admin.reports.*') ? 'bg-[#EAECEF] text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}" @if (request()->routeIs('admin.reports.*')) aria-current="page" @endif>
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linejoin="round" d="M4 19V5m0 14h17"/><path stroke-linejoin="round" d="m7 15 4-4 3 2 6-7"/>
                    </svg>
                    <span class="sidebar-copy sidebar-bottom-label">Reports</span>
                </a>
                <p class="sidebar-copy px-2 pt-2 text-[10px] text-slate-400">ERP modules shown as a UI prototype</p>
            </div>
        </aside>

        @if (request()->routeIs('admin.settings.*', 'admin.users.*'))
            <aside id="settingsSidebar" class="hidden w-[210px] shrink-0 flex-col border-r border-[#E5E7EB] bg-white lg:sticky lg:top-0 lg:flex lg:h-screen">
                <div class="flex h-12 shrink-0 items-center gap-2 border-b border-[#E5E7EB] px-4">
                    <svg class="h-4 w-4 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <rect x="4" y="4" width="16" height="16" rx="2"/><path stroke-linecap="round" d="M9 8h6M9 12h6M9 16h3"/>
                    </svg>
                    <span class="text-[13px] font-medium text-slate-900">Settings</span>
                </div>

                <nav class="min-h-0 flex-1 overflow-y-auto px-2 py-3 text-[12px]" aria-label="Settings navigation">
                    <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">General</p>
                    <div class="space-y-0.5">
                        <button type="button" disabled title="Application settings are not connected" class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">App</button>
                        <a href="{{ route('admin.settings.profile') }}" @if (request()->routeIs('admin.settings.profile*')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.settings.profile*') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Profile</a>
                        <a href="{{ route('admin.settings.security') }}" @if (request()->routeIs('admin.settings.security*')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.settings.security*') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Security</a>
                        <button type="button" disabled title="API settings are not connected" class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">API</button>
                    </div>

                    @if (Auth::user()->canAccessAdminCredentials())
                        <p class="px-2 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Catalog</p>
                        <div class="space-y-0.5">
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">Brand</button>
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">Category</button>
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">Category Type</button>
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">Sub Category</button>
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">UOM</button>
                        </div>

                        <p class="px-2 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Organization</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('admin.settings.companies') }}" @if (request()->routeIs('admin.settings.companies')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.settings.companies') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Company</a>
                            <a href="{{ route('admin.settings.departments') }}" @if (request()->routeIs('admin.settings.departments')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.settings.departments') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Department</a>
                            <a href="{{ route('admin.settings.locations') }}" @if (request()->routeIs('admin.settings.locations')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.settings.locations') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Warehouse/Location</a>
                            <a href="{{ route('admin.settings.partners') }}" @if (request()->routeIs('admin.settings.partners')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.settings.partners') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Supplier/Client</a>
                        </div>

                        <p class="px-2 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wide text-slate-400">System</p>
                        <div class="space-y-0.5">
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">Threshold Settings</button>
                            <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">Approval Flow</button>
                            <a href="{{ route('admin.users.index') }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif class="flex h-8 items-center gap-2 rounded-sm px-2 {{ request()->routeIs('admin.users.*') ? 'bg-[#EAECEF] font-medium text-[#2563EB]' : 'text-slate-700 hover:bg-[#EAECEF]' }}">Users</a>
                        </div>

                        <p class="px-2 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Logs</p>
                        <button type="button" disabled class="flex h-8 w-full cursor-not-allowed items-center gap-2 rounded-sm px-2 text-left text-slate-400">User Logs</button>
                    @endif
                </nav>
            </aside>
        @endif

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex h-12 items-center justify-between border-b border-[#E5E7EB] bg-white px-3 sm:px-5">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" id="sidebarToggle" class="rounded-sm p-1.5 text-slate-600 hover:bg-[#F8F9FA] hover:text-[#2563EB]" aria-label="Toggle navigation" aria-expanded="false">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span class="truncate text-[12px] text-slate-500">
                        @if (request()->routeIs('admin.settings.*', 'admin.users.*'))
                            Settings <span class="px-1 text-slate-300">›</span> <span class="font-medium text-slate-800">@yield('breadcrumb', 'Companies')</span>
                        @elseif (request()->routeIs('admin.reports.*'))
                            <span class="font-medium text-slate-800">Reports</span>
                        @elseif (request()->routeIs('admin.warehouses.*'))
                            Warehouse <span class="px-1 text-slate-300">›</span> <span class="font-medium text-slate-800">@yield('breadcrumb', 'Overview')</span>
                        @else
                            Workspace <span class="px-1 text-slate-300">/</span> <span class="font-medium text-slate-800">@yield('breadcrumb', 'My Dashboard')</span>
                        @endif
                    </span>
                </div>

                <div id="accountMenuRoot" class="relative shrink-0">
                    <button type="button" id="accountMenuToggle" aria-label="Open account menu for {{ Auth::user()->name }}" aria-controls="accountMenu" aria-expanded="false" class="flex items-center gap-2 rounded-sm border border-transparent px-2 py-1 hover:border-[#E5E7EB] hover:bg-[#F8F9FA]">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-[#E5E7EB] bg-[#F8F9FA] text-[12px] font-semibold text-slate-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="hidden max-w-32 truncate text-[12px] font-medium text-slate-700 sm:inline">{{ Auth::user()->name }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
                        </svg>
                    </button>

                    <div id="accountMenu" class="absolute right-0 top-full z-50 mt-2 hidden w-60 border border-[#E5E7EB] bg-white" aria-label="Account menu">
                        <div class="flex items-center gap-2.5 px-3 py-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#E5E7EB] bg-[#F8F9FA] text-[12px] font-semibold text-slate-700" aria-hidden="true">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[12px] font-medium text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="truncate text-[10px] text-slate-500">{{ Auth::user()->email }}</p>
                            </div>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center text-amber-500" title="Light theme active" aria-label="Light theme active">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
                                </svg>
                            </span>
                        </div>

                        <div class="border-t border-[#E5E7EB] py-1">
                            <a href="{{ route('admin.settings.profile') }}" class="flex h-9 items-center gap-2.5 px-3 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0"/>
                                </svg>
                                <span>Profile</span>
                            </a>
                            @if (Auth::user()->canAccessAdminCredentials())
                                <a href="{{ route('admin.settings.companies') }}" class="flex h-9 items-center gap-2.5 px-3 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">
                                    <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m19.4 15 .1.1 1 1.7-1.6 2.8-2-.5a8 8 0 0 1-1.5.9l-.4 2h-3.2l-.4-2a8 8 0 0 1-1.5-.9l-2 .5-1.6-2.8 1-1.7a8 8 0 0 1 0-1.8l-1-1.7L7 5.9l2 .5a8 8 0 0 1 1.5-.9l.4-2h3.2l.4 2a8 8 0 0 1 1.5.9l2-.5 1.6 2.8-1 1.7a8 8 0 0 1 0 1.8Z"/>
                                    </svg>
                                    <span>Settings</span>
                                </a>
                            @endif
                            <a href="{{ route('admin.dashboard') }}#alertsPanel" class="flex h-9 items-center gap-2.5 px-3 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
                                </svg>
                                <span>Notifications</span>
                            </a>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-[#E5E7EB] py-1">
                            @csrf
                            <button type="submit" class="flex h-9 w-full items-center gap-2.5 px-3 text-left text-[12px] text-slate-700 hover:bg-[#F8F9FA]">
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
                                </svg>
                                <span>Log out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="min-w-0 bg-white">
                <div class="space-y-3 px-3 pt-3 sm:px-5">
                    @if (session('status'))
                        <div role="status" class="flex items-center justify-between gap-3 border border-[#E5E7EB] bg-[#F8F9FA] px-3 py-2 text-[12px] text-slate-700">
                            <span>{{ session('status') }}</span>
                            <button type="button" onclick="this.parentElement.remove()" class="px-1 text-slate-500" aria-label="Dismiss message">×</button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="border border-[#E5E7EB] bg-[#F8F9FA] px-3 py-2 text-[12px] text-slate-700">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
                @yield('content')
            </main>
        </div>
    </div>

    @yield('modals')

    <script>
        const sidebar = document.getElementById('dashboardSidebar');
        const sidebarBackdrop = document.getElementById('dashboardSidebarBackdrop');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const accountMenuRoot = document.getElementById('accountMenuRoot');
        const accountMenu = document.getElementById('accountMenu');
        const accountMenuToggle = document.getElementById('accountMenuToggle');

        const closeMobileSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.add('hidden');
            sidebarToggle.setAttribute('aria-expanded', 'false');
        };

        const closeAccountMenu = () => {
            accountMenu.classList.add('hidden');
            accountMenuToggle.setAttribute('aria-expanded', 'false');
        };

        accountMenuToggle.addEventListener('click', () => {
            const isExpanded = accountMenuToggle.getAttribute('aria-expanded') === 'true';
            accountMenu.classList.toggle('hidden', isExpanded);
            accountMenuToggle.setAttribute('aria-expanded', String(!isExpanded));
        });

        document.addEventListener('click', (event) => {
            if (!accountMenuRoot.contains(event.target)) {
                closeAccountMenu();
            }
        });

        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                sidebar.classList.toggle('-translate-x-full', isOpen);
                sidebarBackdrop.classList.toggle('hidden', isOpen);
                sidebarToggle.setAttribute('aria-expanded', String(!isOpen));
                return;
            }

            const isCollapsed = sidebar.classList.toggle('is-collapsed');
            localStorage.setItem('operations_sidebar_collapsed', isCollapsed ? '1' : '0');
        });

        document.getElementById('sidebarClose').addEventListener('click', closeMobileSidebar);
        sidebarBackdrop.addEventListener('click', closeMobileSidebar);

        if (window.innerWidth >= 1024 && localStorage.getItem('operations_sidebar_collapsed') === '1') {
            sidebar.classList.add('is-collapsed');
        }

        document.querySelectorAll('[data-menu-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const panel = document.getElementById(toggle.getAttribute('aria-controls'));
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                panel.hidden = expanded;
                panel.classList.toggle('hidden', expanded);
                toggle.setAttribute('aria-expanded', String(!expanded));
            });
        });

        document.querySelectorAll('[data-dashboard-tab]').forEach((button) => {
            button.addEventListener('click', () => {
                const selectedTab = button.dataset.dashboardTab;

                document.querySelectorAll('[data-dashboard-tab]').forEach((tab) => {
                    const selected = tab === button;
                    tab.setAttribute('aria-pressed', String(selected));
                    tab.classList.toggle('bg-[#EAECEF]', selected);
                    tab.classList.toggle('text-slate-900', selected);
                    tab.classList.toggle('text-slate-500', !selected);
                });

                document.querySelectorAll('[data-dashboard-panel]').forEach((panel) => {
                    panel.hidden = panel.dataset.dashboardPanel !== selectedTab;
                });
            });
        });

        const openAlertsFromHash = () => {
            if (window.location.hash === '#alertsPanel') {
                const alertsTab = document.querySelector('[data-dashboard-tab="alerts"]');

                if (alertsTab) {
                    alertsTab.click();
                }
            }
        };

        window.addEventListener('hashchange', openAlertsFromHash);
        openAlertsFromHash();

        const dateElement = document.getElementById('dashboardDate');
        const timeElement = document.getElementById('dashboardClock');
        const greetingElement = document.getElementById('dashboardGreeting');
        const dateFormatter = new Intl.DateTimeFormat('en-US', {
            weekday: 'long',
            month: 'long',
            day: '2-digit',
            year: 'numeric',
        });
        const timeFormatter = new Intl.DateTimeFormat('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });

        const updateClock = () => {
            const now = new Date();

            if (dateElement) {
                dateElement.textContent = dateFormatter.format(now).toUpperCase();
            }

            if (timeElement) {
                timeElement.textContent = timeFormatter.format(now);
            }

            if (greetingElement) {
                const greeting = now.getHours() < 12 ? 'Good Day' : 'Good Afternoon';
                greetingElement.textContent = `${greeting} ${greetingElement.dataset.firstName}!`;
            }
        };

        updateClock();
        window.setInterval(updateClock, 1000);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAccountMenu();
                closeMobileSidebar();
                document.querySelectorAll('[data-modal]').forEach((modal) => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
