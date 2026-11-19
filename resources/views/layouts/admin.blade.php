<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white text-slate-800">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | Globaltronics ICS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/globaltronics_logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        .warehouse-pattern {
            background-image: radial-gradient(rgba(15, 23, 42, 0.06) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .glass-panel {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 1);
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 1);
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.8);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.6);
        }

        /* Collapsible Sidebar Styles */
        #sidebarMenu {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: width;
        }
        #sidebarMenu.collapsed {
            width: 4.75rem !important; /* 76px */
        }
        #sidebarMenu.collapsed .sidebar-text,
        #sidebarMenu.collapsed .sidebar-badge,
        #sidebarMenu.collapsed .sidebar-heading,
        #sidebarMenu.collapsed .sidebar-footer,
        #sidebarMenu.collapsed .sidebar-submenu,
        #sidebarMenu.collapsed .sidebar-chevron {
            display: none !important;
        }
        #sidebarMenu.collapsed .sidebar-item {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        #sidebarMenu.collapsed .sidebar-icon {
            margin: 0 !important;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-white text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-200 bg-white/95 backdrop-blur-xl">
        <div class="px-4 sm:px-6 lg:px-8 flex h-16 items-center justify-between gap-4">
            
            <!-- Left: Logo & System Title -->
            <div class="flex items-center gap-3.5 shrink-0">
                <button type="button" onclick="toggleSidebar()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer" title="Toggle Sidebar (Collapse / Expand)" aria-label="Toggle Sidebar">
                    <svg id="sidebarToggleIcon" class="w-5 h-5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 p-1.5 flex items-center justify-center shadow-sm group-hover:border-blue-500/50 transition-colors shrink-0">
                        <img src="{{ asset('assets/images/logo/globaltronics_logo.png') }}" alt="Globaltronics" class="w-full h-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold tracking-tight text-slate-900 text-sm sm:text-base">GLOBALTRONICS</span>
                            <span class="hidden sm:inline-block px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-amber-50 text-amber-700 border border-amber-300">OPERATIONS</span>
                        </div>
                        <p class="text-[10px] uppercase tracking-wider text-slate-500 font-medium">Warehouse Management System</p>
                    </div>
                </a>

            </div>

            <!-- Middle: Facility & Status Pill (Desktop) -->
            <div class="hidden md:flex items-center gap-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-50 border border-slate-200 text-xs text-slate-700">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="font-mono-code text-[11px] text-emerald-600 font-bold">WMS LIVE</span>
                    <span class="text-slate-300">|</span>
                    <span class="inline-flex items-center gap-1.5 font-mono-code text-[11px] text-slate-700 font-semibold" title="Active users in last 5 minutes">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $onlineUsersCount ?? 1 }} Online
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-500 text-[11px]">Hub: Globaltronics Warehouse</span>
                </div>
            </div>

            <!-- Right: Admin User Profile Dropdown & Actions -->
            <div class="flex items-center gap-3">
                <div class="relative flex items-center gap-2 sm:gap-3">
                    <!-- Notification Bell Dropdown (Between Name and Actions) -->
                    <div class="relative" id="notificationDropdownContainer">
                        <button type="button" 
                                onclick="toggleNotificationDropdown()" 
                                id="notificationBellBtn"
                                class="relative p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 shadow-xs transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                title="Transaction Notifications" 
                                aria-label="Transaction Notifications">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if (!empty($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-mono-code font-bold text-white shadow-xs animate-pulse">
                                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown Drawer -->
                        <div id="notificationDropdownMenu" class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white border border-slate-200 shadow-2xl py-3 z-50 animate-fadeIn">
                            <div class="px-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900">Activity &amp; Transactions</span>
                                    @if (!empty($unreadNotificationCount) && $unreadNotificationCount > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                            {{ $unreadNotificationCount }} new
                                        </span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">
                                        Mark all read
                                    </button>
                                </form>
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 px-1 py-1">
                                @forelse ($headerNotifications ?? [] as $notif)
                                    <div class="p-3 rounded-xl transition-colors {{ $notif->is_read ? 'hover:bg-slate-50 opacity-80' : 'bg-blue-50/40 hover:bg-blue-50/70 border-l-2 border-blue-500' }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="font-bold text-xs text-slate-900 leading-snug">{{ $notif->title }}</span>
                                            <span class="text-[10px] font-mono-code text-slate-400 shrink-0">{{ $notif->created_at->diffForHumans(null, true, true) }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">{{ $notif->message }}</p>
                                        <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400 font-mono-code">
                                            <span>Actor: <strong class="text-slate-700">{{ $notif->actor_name }}</strong></span>
                                            @if (!$notif->is_read)
                                                <form method="POST" action="{{ route('admin.notifications.read', $notif) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:underline">Mark read</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-slate-400">
                                        <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        No recent transactions recorded yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name }}</span>
                        <span class="text-[11px] text-blue-600 font-mono-code font-medium">
                            {{ Auth::user()->roles->first()?->name ?? 'Administrator' }}
                        </span>
                    </div>

                    <!-- Admin Credentials (Users, Roles & Clearance) Icon-Only Button - Restricted to IT Admin -->
                    @if (Auth::user()->canAccessAdminCredentials())
                        <a href="{{ route('admin.settings.credentials') }}" 
                           class="p-2.5 rounded-xl border {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') ? 'bg-blue-50 border-blue-300 text-blue-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50' }} transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           title="Admin Credentials (User Accounts, Roles & Security)" 
                           aria-label="Admin Credentials">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </a>
                    @endif

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 text-slate-500 hover:text-rose-600 hover:border-rose-200 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500" title="Log out" aria-label="Log out">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </header>

    <div class="flex-1 flex min-w-0 relative">
        
        <!-- Mobile Sidebar Backdrop -->
        <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 top-16 bg-slate-900/40 backdrop-blur-xs z-30 hidden lg:hidden transition-opacity"></div>

        <!-- SIDEBAR -->
        <aside id="sidebarMenu" class="fixed top-16 bottom-0 left-0 z-40 w-72 bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:overflow-y-auto lg:z-30 transition-all duration-300 flex flex-col overflow-x-hidden shrink-0 shadow-lg lg:shadow-none">
            <div class="px-3 pt-0 pb-4 sm:px-3.5 sm:pt-0 sm:pb-4 flex flex-col gap-2">
                <!-- Mobile Sidebar Close -->
                <div class="flex items-center justify-between lg:hidden border-b border-slate-200 py-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Navigation Menu</span>
                    <button type="button" onclick="toggleSidebar()" class="p-1 text-slate-500 hover:text-slate-900" aria-label="Close Sidebar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Section: Management -->
                <div>
                    <p class="sidebar-heading px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest m-0 mb-1 font-mono-code">Navigation</p>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span class="sidebar-text">Dashboard</span>
                        </a>
                        @if (Auth::user()->canManageSrf())
                            <button type="button" onclick="if (typeof openInstallationSrfModal === 'function') { openInstallationSrfModal(); } else { window.location.href = '{{ route('admin.dashboard') }}?open_srf=1'; }" class="sidebar-item w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                <span class="sidebar-text">Create SRF</span>
                            </button>
                        @endif
                        <a href="{{ route('admin.srf.approved') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.srf.approved') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span class="sidebar-text">Approved SRFs</span>
                        </a>
                        @if (Auth::user()->canAccessAdminCredentials())
                            <a href="{{ route('admin.settings.credentials') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                <span class="sidebar-text">Manage accounts &amp; roles</span>
                            </a>
                        @endif
                        <a href="{{ route('home') }}" target="_blank" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            <span class="sidebar-text">Login portal</span>
                        </a>
                    </nav>
                </div>

                <!-- Merged System Info Pill in Sidebar Menu -->
                <div class="sidebar-footer pt-3 mt-1 border-t border-slate-200">
                    <div class="p-3 rounded-xl bg-slate-50/90 border border-slate-200 text-xs">
                        <div class="flex items-center justify-between text-slate-500 mb-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span class="font-semibold text-slate-700 text-[11px]">System Environment</span>
                            </div>
                            <span class="font-mono-code text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60">STABLE</span>
                        </div>
                        <div class="space-y-0.5 text-[11px] font-mono-code text-slate-500">
                            <p>Laravel: v{{ app()->version() }}</p>
                            <p>PHP: v{{ PHP_VERSION }}</p>
                            <p>DB: SQLite</p>
                        </div>
                    </div>
                </div>

            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 overflow-y-auto min-w-0 bg-slate-50/70 px-4 pt-3 pb-8 sm:px-6 sm:pt-4 sm:pb-8 lg:px-8 lg:pt-4 lg:pb-8 warehouse-pattern">
            <div class="max-w-[1920px] w-full mx-auto space-y-6">

                <!-- Flash Alert Messages -->
                @if (session('status'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between gap-3 shadow-sm animate-fadeIn">
                        <div class="flex items-center gap-3">
                            <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="font-medium">{{ session('status') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 p-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm animate-fadeIn">
                        <div class="flex items-start gap-3">
                            <span class="p-1 rounded-lg bg-rose-100 text-rose-700 mt-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </span>
                            <div class="flex-1">
                                <span class="font-bold">Please correct the following errors:</span>
                                <ul class="mt-1 list-disc list-inside space-y-0.5 text-xs text-rose-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-rose-700 hover:text-rose-950 p-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Main View Content -->
                @yield('content')

            </div>
        </main>
    </div>

    <!-- Modals Yield Section -->
    @yield('modals')

    <script>
        function updateSidebarState(isCollapsed) {
            const sidebar = document.getElementById('sidebarMenu');
            const icon = document.getElementById('sidebarToggleIcon');
            if (!sidebar) return;

            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                if (icon) icon.classList.add('rotate-180');
            } else {
                sidebar.classList.remove('collapsed');
                if (icon) icon.classList.remove('rotate-180');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (!sidebar) return;

            const isMobile = window.innerWidth < 1024;

            if (isMobile) {
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    if (backdrop) backdrop.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    if (backdrop) backdrop.classList.add('hidden');
                }
            } else {
                const nowCollapsed = !sidebar.classList.contains('collapsed');
                updateSidebarState(nowCollapsed);
                localStorage.setItem('admin_sidebar_collapsed', nowCollapsed ? '1' : '0');
            }
        }

        // Restore saved sidebar collapse preference on desktop
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth >= 1024 && localStorage.getItem('admin_sidebar_collapsed') === '1') {
                updateSidebarState(true);
            }
        });

        function toggleNotificationDropdown() {
            const menu = document.getElementById('notificationDropdownMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('notificationDropdownContainer');
            const menu = document.getElementById('notificationDropdownMenu');
            if (container && menu && !container.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Auto-open SRF modal only when specifically requested via URL (e.g. from another page) and clean URL immediately
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('open_srf') === '1') {
                urlParams.delete('open_srf');
                const newQuery = urlParams.toString();
                const newUrl = window.location.pathname + (newQuery ? '?' + newQuery : '') + window.location.hash;
                window.history.replaceState({}, document.title, newUrl);
                if (typeof openInstallationSrfModal === 'function') {
                    openInstallationSrfModal();
                }
            }
        });

        // Close any modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const notifMenu = document.getElementById('notificationDropdownMenu');
                if (notifMenu) notifMenu.classList.add('hidden');
                document.querySelectorAll('[data-modal]').forEach(modal => {
                    modal.classList.add('hidden');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
