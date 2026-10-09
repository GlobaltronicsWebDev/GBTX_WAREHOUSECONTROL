@extends('layouts.admin')

@section('title', 'Warehouse Inventory - Centralized LED & Units | Globaltronics ICS')

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Page Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono-code text-blue-600 mb-1">
                <span>TERMINAL ID: WMS-ADM-01</span>
                <span>•</span>
                <span class="text-emerald-600 font-semibold">INVENTORY CONTROL ACTIVE</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3 flex-wrap">
                <span>Warehouse Inventory System</span>
                @php
                    $isServiceUnitCategory = in_array($selectedCategory, array_merge($serviceUnitSubCategories ?? [], ['Service Units (Events, Demo)', 'SERVICE UNITS (EVENTS, DEMO)', 'Service Units', 'SERVICE UNITS']));
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono-code font-bold {{ $selectedCategory === 'CENTRALIZED LED INVENTORY' ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($selectedCategory === 'EOL PHILIPS UNITS' ? 'bg-cyan-100 text-cyan-800 border border-cyan-300' : ($isServiceUnitCategory ? 'bg-purple-100 text-purple-900 border border-purple-300' : 'bg-blue-100 text-blue-900 border border-blue-300')) }}">
                    {{ $selectedCategory === 'CENTRALIZED LED INVENTORY' ? 'LED Inventory' : ($selectedCategory === 'EOL PHILIPS UNITS' ? 'Philips Units' : ($isServiceUnitCategory ? 'Service Units: ' . $selectedCategory : 'All Inventory')) }}
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Centralized management for LED display modules, panels, SQM coverage, and warehouse facilities (Globaltronics & AJUAN).
            </p>
        </div>

        <!-- Quick Action: Add Inventory Unit -->
        <div class="flex items-center gap-2.5">
            <button 
                type="button" 
                onclick="openCreateModal('{{ $selectedCategory !== 'all' ? $selectedCategory : 'CENTRALIZED LED INVENTORY' }}')" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.01]"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Inventory Item</span>
            </button>
        </div>
    </div>

    <!-- LIVE INVENTORY METRICS TILES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Units in Category -->
        <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Quantity</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 font-mono-code">{{ number_format($totalUnits) }}</span>
                <span class="text-xs text-emerald-600 font-semibold">{{ $selectedCategory === 'CENTRALIZED LED INVENTORY' ? 'LED Cabinets' : 'Units' }}</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ $totalModels }} active SKU lines recorded</p>
        </div>

        <!-- Total SQM (if LED) or Overall Facility Units -->
        @if ($selectedCategory === 'CENTRALIZED LED INVENTORY')
            <div class="glass-card bg-white p-5 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50/40 via-white to-white shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Total Area (SQM)</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center text-amber-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-amber-700 font-mono-code">{{ number_format($totalSqm, 2) }}</span>
                    <span class="text-xs text-amber-800 font-semibold">m² Total Display</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Combined square meter inventory capacity</p>
            </div>
        @else
            <div class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Overall Warehouse Units</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-blue-700 font-mono-code">{{ number_format($overallUnits) }}</span>
                    <span class="text-xs text-blue-600 font-semibold">All Categories</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Across all warehouses & partner facilities</p>
            </div>
        @endif

        <!-- Globaltronics Facility Stock -->
        <a href="{{ route('admin.inventory.index', ['location' => 'Globaltronics', 'category' => $selectedCategory]) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Globaltronics Facility</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-blue-700 font-mono-code">{{ number_format($globaltronicsUnits) }}</span>
                <span class="text-xs text-blue-600 font-semibold group-hover:underline">Units In Stock →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Main warehouse facility inventory</p>
        </a>

        <!-- AJUAN Facility Stock -->
        <a href="{{ route('admin.inventory.index', ['location' => 'AJUAN', 'category' => $selectedCategory]) }}" class="glass-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-purple-400 hover:shadow-md transition-all block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">AJUAN Facility</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-purple-700 font-mono-code">{{ number_format($ajuanUnits) }}</span>
                <span class="text-xs text-purple-600 font-semibold group-hover:underline">Units In Stock →</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">AJUAN partner warehouse branch</p>
        </a>

    </div>

    <!-- Category Tabs & Filters Bar -->
    <div class="glass-panel bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3.5">
        
        <!-- Top Row: Category Tabs & Quick Location Pil        <!-- Top Row: Category Tabs & Quick Location Pills -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Centralized LED Tab -->
                <a 
                    href="{{ route('admin.inventory.index', ['category' => 'CENTRALIZED LED INVENTORY', 'location' => $selectedLocation]) }}" 
                    class="px-3.5 py-2 rounded-xl text-xs font-bold tracking-wide uppercase transition-all shrink-0 flex items-center gap-2 {{ $selectedCategory === 'CENTRALIZED LED INVENTORY' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    <span class="w-2 h-2 rounded-full {{ $selectedCategory === 'CENTRALIZED LED INVENTORY' ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                    <span>CENTRALIZED LED INVENTORY</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono-code {{ $selectedCategory === 'CENTRALIZED LED INVENTORY' ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-700' }}">
                        {{ $ledCount }}
                    </span>
                </a>

                <!-- EOL Philips Units Tab -->
                <a 
                    href="{{ route('admin.inventory.index', ['category' => 'EOL PHILIPS UNITS', 'location' => $selectedLocation]) }}" 
                    class="px-3.5 py-2 rounded-xl text-xs font-bold tracking-wide uppercase transition-all shrink-0 flex items-center gap-2 {{ $selectedCategory === 'EOL PHILIPS UNITS' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    <span class="w-2 h-2 rounded-full {{ $selectedCategory === 'EOL PHILIPS UNITS' ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                    <span>EOL PHILIPS UNITS</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono-code {{ $selectedCategory === 'EOL PHILIPS UNITS' ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-700' }}">
                        {{ $philipsCount }}
                    </span>
                </a>

                <!-- Service Units (Events, Demo) Tab -->
                @php
                    $isServiceUnitsTabActive = in_array($selectedCategory, array_merge($serviceUnitSubCategories ?? [], ['Service Units (Events, Demo)', 'SERVICE UNITS (EVENTS, DEMO)', 'Service Units', 'SERVICE UNITS']));
                @endphp
                <a 
                    href="{{ route('admin.inventory.index', ['category' => 'Service Units (Events, Demo)', 'location' => $selectedLocation]) }}" 
                    class="px-3.5 py-2 rounded-xl text-xs font-bold tracking-wide uppercase transition-all shrink-0 flex items-center gap-2 {{ $isServiceUnitsTabActive ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    <span class="w-2 h-2 rounded-full {{ $isServiceUnitsTabActive ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                    <span>SERVICE UNITS (EVENTS, DEMO)</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono-code {{ $isServiceUnitsTabActive ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-700' }}">
                        {{ $serviceUnitsCount ?? 0 }}
                    </span>
                </a>

                <!-- All Categories -->
                <a 
                    href="{{ route('admin.inventory.index', ['category' => 'all', 'location' => $selectedLocation]) }}" 
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $selectedCategory === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    All Categories
                </a>
            </div>

            <!-- Quick Location Filter Chips -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Quick:</span>
                <a 
                    href="{{ route('admin.inventory.index', ['category' => $selectedCategory, 'location' => 'Globaltronics']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors shrink-0 {{ $selectedLocation === 'Globaltronics' ? 'bg-slate-900 text-white font-bold' : 'text-slate-500 hover:bg-slate-100' }}"
                >
                    Globaltronics ({{ $globaltronicsUnits }})
                </a>
                <a 
                    href="{{ route('admin.inventory.index', ['category' => $selectedCategory, 'location' => 'AJUAN']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors shrink-0 {{ $selectedLocation === 'AJUAN' ? 'bg-slate-900 text-white font-bold' : 'text-slate-500 hover:bg-slate-100' }}"
                >
                    AJUAN ({{ $ajuanUnits }})
                </a>
            </div>
        </div>

        @if ($isServiceUnitsTabActive)
            <!-- Service Units Sub-Categories Filter Strip -->
            <div class="flex flex-wrap items-center gap-1.5 pt-2 pb-1 border-t border-slate-200 text-xs bg-slate-50/70 p-2.5 rounded-xl">
                <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mr-1">Sub-Categories:</span>
                <a 
                    href="{{ route('admin.inventory.index', ['category' => 'Service Units (Events, Demo)', 'location' => $selectedLocation]) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $selectedCategory === 'Service Units (Events, Demo)' || $selectedCategory === 'SERVICE UNITS (EVENTS, DEMO)' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' }}"
                >
                    All Service Units ({{ $serviceUnitsCount ?? 0 }})
                </a>
                @foreach ($serviceUnitSubCategories ?? [] as $sub)
                    @php
                        $subC = $subCategoryCounts[$sub] ?? 0;
                    @endphp
                    <a 
                        href="{{ route('admin.inventory.index', ['category' => $sub, 'location' => $selectedLocation]) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all {{ $selectedCategory === $sub ? 'bg-slate-800 text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' }}"
                    >
                        {{ $sub }} ({{ $subC }})
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Bottom Row: Search Form & Dropdowns Perfectly Aligned in One Line -->
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
            <input type="hidden" name="category" value="{{ $selectedCategory }}">

            <!-- Location Dropdown Filter -->
            <select 
                name="location" 
                onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">All Locations</option>
                @foreach ($availableLocations as $loc)
                    <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                @endforeach
            </select>

            <!-- Manufacturer Dropdown Filter -->
            <select 
                name="manufacturer" 
                onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">All Manufacturers</option>
                @foreach ($manufacturersList as $mfg)
                    <option value="{{ $mfg }}" {{ $selectedManufacturer === $mfg ? 'selected' : '' }}>{{ $mfg }}</option>
                @endforeach
            </select>

            <!-- Screen Size Filter (only if Philips or All) -->
            @if ($selectedCategory !== 'CENTRALIZED LED INVENTORY' && count($screenSizes) > 0)
                <select 
                    name="screen_size" 
                    onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Sizes</option>
                    @foreach ($screenSizes as $size)
                        <option value="{{ $size }}" {{ $selectedScreenSize === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Search Keyword Input -->
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search model, description, location..." 
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                >
            </div>

            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Filter
            </button>

            @if($search || $selectedLocation || $selectedManufacturer || $selectedScreenSize)
                <a href="{{ route('admin.inventory.index', ['category' => $selectedCategory]) }}" class="px-2 py-1 text-xs text-rose-600 hover:underline">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- MAIN SPREADSHEET TABLE CARD (Clean & Simple Style) -->
    <div class="glass-panel bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Clean & Simple Title Banner with Column Group Toggles -->
        <div class="bg-white border-b border-slate-200 py-3.5 px-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-left">
                <h2 class="text-lg sm:text-xl font-bold tracking-wide text-slate-900 uppercase select-none">
                    @if ($selectedCategory === 'LED Service Units' || $selectedCategory === 'LED SERVICES UNITS')
                        LED SERVICE UNITS
                    @elseif ($selectedCategory === 'Philips Service Units' || $selectedCategory === 'PHILIPS SERVICE UNITS')
                        PHILIPS SERVICE UNITS
                    @elseif ($selectedCategory === 'CENTRALIZED LED INVENTORY')
                        CENTRALIZED LED INVENTORY
                    @elseif ($selectedCategory === 'EOL PHILIPS UNITS')
                        EOL PHILIPS UNITS
                    @elseif ($isServiceUnitsTabActive)
                        {{ strtoupper($selectedCategory) }}
                    @else
                        ALL WAREHOUSE INVENTORY
                    @endif
                </h2>
                <p class="text-xs text-slate-500">Click any row to open the full item drawer & movement logs</p>
            </div>

            <!-- Column View Mode Selectors (LuminaSpec Reference) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <span class="text-xs text-slate-500 font-semibold mr-1 whitespace-nowrap">View Columns:</span>
                <button type="button" onclick="setColumnView('all')" id="btnViewAll" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white transition whitespace-nowrap shadow-sm" style="background-color: #0284c7 !important; color: #ffffff !important;">All</button>
                <button type="button" onclick="setColumnView('specs')" id="btnViewSpecs" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition whitespace-nowrap">Specs</button>
                <button type="button" onclick="setColumnView('inventory')" id="btnViewInventory" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition whitespace-nowrap">Inventory & SQM</button>
                <button type="button" onclick="setColumnView('reservation')" id="btnViewReservation" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition whitespace-nowrap">Reservations</button>
                <button type="button" onclick="setColumnView('history')" id="btnViewHistory" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition whitespace-nowrap">Status & History</button>
            </div>
        </div>

        <!-- Spreadsheet Grid Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1300px] text-left border-collapse">
                
                @if ($selectedCategory === 'LED Service Units' || $selectedCategory === 'LED SERVICES UNITS')
                    <!-- CLEAN 2-TIER SPREADSHEET HEADER FOR LED SERVICE UNITS -->
                    <thead>
                        <tr class="bg-slate-50 text-slate-700 font-bold uppercase text-[11px] sm:text-xs tracking-wider divide-x divide-slate-200 border-b border-slate-200">
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none w-16 whitespace-nowrap">CDX</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">LOCATION</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">MANUFACTURER</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[120px] whitespace-nowrap">CHECK IN DATE</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">MODEL</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">PO / SKU No.</th>
                            <th rowspan="2" class="py-2.5 px-4 text-left text-slate-700 select-none min-w-[320px]">ITEM DESCRIPTION</th>
                            <th colspan="4" class="py-1 px-3 text-center text-slate-700 select-none border-b border-slate-200 whitespace-nowrap">INVENTORY</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[90px] whitespace-nowrap">ACTIONS</th>
                        </tr>
                        <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider divide-x divide-slate-200 border-b border-slate-200">
                            <th class="py-1.5 px-2.5 text-center text-slate-600 select-none min-w-[65px] whitespace-nowrap">QTY</th>
                            <th class="py-1.5 px-2.5 text-center text-slate-600 select-none min-w-[75px] whitespace-nowrap">ACU. QTY</th>
                            <th class="py-1.5 px-2.5 text-center text-slate-600 select-none min-w-[110px] whitespace-nowrap">PER PANEL SQM</th>
                            <th class="py-1.5 px-2.5 text-center text-slate-600 select-none min-w-[140px] whitespace-nowrap">TOTAL AVAILABLE SQM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-xs sm:text-sm font-medium">
                        @forelse ($items as $idx => $item)
                            <tr onclick="openDrawer({{ json_encode($item) }})" class="hover:bg-blue-50/70 transition-colors cursor-pointer group">
                                <!-- CDX -->
                                <td class="py-3 px-2 font-mono-code font-bold text-center text-slate-800 border-r border-slate-200 whitespace-nowrap">
                                    {{ $item->tag_number ?? ($idx + 1) }}
                                </td>

                                <!-- LOCATION -->
                                <td class="py-3 px-3 text-center border-r border-slate-200 font-mono-code whitespace-nowrap">
                                    @if (stripos($item->location, 'MARIKINA') !== false)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $item->location }}
                                        </span>
                                    @elseif (stripos($item->location, 'GLOBALTRONICS') !== false)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $item->location }}
                                        </span>
                                    @elseif (stripos($item->location, '1ST FLR') !== false || stripos($item->location, '1ST FLOOR') !== false || stripos($item->location, 'AJUAN') !== false || stripos($item->location, 'A JUAN') !== false)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $item->location }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $item->location }}
                                        </span>
                                    @endif
                                </td>

                                <!-- MANUFACTURER -->
                                <td class="py-3 px-3 font-semibold text-slate-800 uppercase font-mono-code border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->manufacturer }}
                                </td>

                                <!-- CHECK IN DATE -->
                                <td class="py-3 px-3 font-mono-code text-center text-slate-600 border-r border-slate-200 whitespace-nowrap">
                                    {{ $item->check_in_date ? $item->check_in_date->format('n/j/Y') : '—' }}
                                </td>

                                <!-- MODEL -->
                                <td class="py-3 px-3 font-mono-code font-semibold text-slate-900 border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->model }}
                                </td>

                                <!-- PO / SKU No. -->
                                <td class="py-3 px-3 font-mono-code text-center text-slate-600 border-r border-slate-200 whitespace-nowrap">
                                    {{ $item->po_number ?? '—' }}
                                </td>

                                <!-- ITEM DESCRIPTION -->
                                <td class="py-3 px-4 text-slate-800 uppercase font-medium leading-relaxed border-r border-slate-200 min-w-[320px]">
                                    {{ $item->item_description }}
                                </td>

                                <!-- QTY -->
                                <td class="py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->quantity }}
                                    </span>
                                </td>

                                <!-- ACU. QTY -->
                                <td class="py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->quantity }}
                                    </span>
                                </td>

                                <!-- PER PANEL SQM -->
                                <td class="py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="font-medium text-xs text-slate-600">
                                        {{ $item->sqm && $item->quantity > 0 ? number_format($item->sqm / $item->quantity, 3) : '—' }}
                                    </span>
                                </td>

                                <!-- TOTAL AVAILABLE SQM -->
                                <td class="py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] px-1.5 py-0.5 rounded font-semibold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->sqm !== null ? number_format($item->sqm, 2) : '—' }}
                                    </span>
                                </td>

                                <!-- ACTIONS -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openEditModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Edit Item Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openDeleteModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Delete Item Record"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="py-12 text-center text-slate-500">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-bold text-slate-700">No LED service units found</p>
                                        <p class="text-xs text-slate-400">Add an LED service unit using the button above to populate this list.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                @elseif ($selectedCategory === 'Philips Service Units' || $selectedCategory === 'PHILIPS SERVICE UNITS')
                    <!-- CLEAN 2-TIER SPREADSHEET HEADER FOR PHILIPS SERVICE UNITS -->
                    <thead>
                        <tr class="bg-slate-50 text-slate-700 font-bold uppercase text-[11px] sm:text-xs tracking-wider divide-x divide-slate-200 border-b border-slate-200">
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">LOCATION</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">MANUFACTURER</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[120px] whitespace-nowrap">CHECK IN DATE</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">MODEL</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">PO / SKU No.</th>
                            <th colspan="2" class="py-1 px-3 text-center text-slate-700 select-none border-b border-slate-200 whitespace-nowrap">ITEM DESCRIPTION</th>
                            <th colspan="2" class="py-1 px-3 text-center text-slate-700 select-none border-b border-slate-200 whitespace-nowrap">INVENTORY</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[110px] whitespace-nowrap">AVAILABLE QTY</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[90px] whitespace-nowrap">ACTIONS</th>
                        </tr>
                        <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider divide-x divide-slate-200 border-b border-slate-200">
                            <th class="py-1.5 px-4 text-left text-slate-600 select-none min-w-[260px]">PARTICULAR</th>
                            <th class="py-1.5 px-3 text-center text-slate-600 select-none min-w-[130px] whitespace-nowrap">SERIAL NO.</th>
                            <th class="py-1.5 px-2.5 text-center text-slate-600 select-none min-w-[65px] whitespace-nowrap">QTY</th>
                            <th class="py-1.5 px-2.5 text-center text-slate-600 select-none min-w-[75px] whitespace-nowrap">ACU. QTY</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-xs sm:text-sm font-medium">
                        @forelse ($items as $item)
                            <tr onclick="openDrawer({{ json_encode($item) }})" class="hover:bg-blue-50/70 transition-colors cursor-pointer group">
                                <!-- LOCATION -->
                                <td class="py-3 px-3 text-center border-r border-slate-200 font-mono-code whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->location }}
                                    </span>
                                </td>

                                <!-- MANUFACTURER -->
                                <td class="py-3 px-3 font-semibold text-slate-800 uppercase font-mono-code border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->manufacturer }}
                                </td>

                                <!-- CHECK IN DATE -->
                                <td class="py-3 px-3 font-mono-code text-center text-slate-600 border-r border-slate-200 whitespace-nowrap">
                                    {{ $item->check_in_date ? $item->check_in_date->format('n/j/Y') : '—' }}
                                </td>

                                <!-- MODEL -->
                                <td class="py-3 px-3 font-mono-code font-semibold text-slate-900 border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->model }}
                                </td>

                                <!-- PO / SKU No. -->
                                <td class="py-3 px-3 font-mono-code text-center text-slate-600 border-r border-slate-200 whitespace-nowrap">
                                    {{ $item->po_number ?? '—' }}
                                </td>

                                <!-- ITEM DESCRIPTION: PARTICULAR -->
                                <td class="py-3 px-4 text-slate-800 uppercase font-medium leading-relaxed border-r border-slate-200 min-w-[260px]">
                                    {{ $item->item_description }}
                                </td>

                                <!-- ITEM DESCRIPTION: SERIAL NO. -->
                                <td class="py-2.5 px-3 font-mono-code font-bold text-center text-slate-800 border-r border-slate-200">
                                    @if ($item->tag_number)
                                        @php
                                            $serials = preg_split('/[\r\n,;|]+/', $item->tag_number, -1, PREG_SPLIT_NO_EMPTY);
                                        @endphp
                                        <div class="flex flex-col items-center justify-center space-y-1">
                                            @foreach ($serials as $sn)
                                                <span class="text-xs font-mono-code font-bold italic text-slate-900 whitespace-nowrap block">
                                                    {{ trim($sn) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- INVENTORY: QTY -->
                                <td class="py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->quantity }}
                                    </span>
                                </td>

                                <!-- INVENTORY: ACU. QTY -->
                                <td class="py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->acu_quantity ?? $item->quantity }}
                                    </span>
                                </td>

                                <!-- AVAILABLE QTY -->
                                <td class="py-3 px-3 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs {{ $item->forecasted_quantity ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-slate-400' }}">
                                        {{ $item->forecasted_quantity ?? '—' }}
                                    </span>
                                </td>

                                <!-- ACTIONS -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openEditModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Edit Item Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openDeleteModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Delete Item Record"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-500">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-bold text-slate-700">No Philips service units found</p>
                                        <p class="text-xs text-slate-400">Add a Philips service unit using the button above to populate this list.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                @elseif ($selectedCategory === 'CENTRALIZED LED INVENTORY')
                    <!-- CLEAN SPREADSHEET HEADER FOR CENTRALIZED LED WITH EXPANDED GROUPS -->
                    <thead>
                        <tr class="bg-slate-50 text-slate-700 font-bold uppercase text-[11px] sm:text-xs tracking-wider divide-x divide-slate-200 border-b border-slate-200">
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-3 text-center text-slate-700 select-none min-w-[100px] whitespace-nowrap">TAG #</th>
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-3 text-center text-slate-700 select-none min-w-[110px] whitespace-nowrap">DATE RECEIVED</th>
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-3 text-center text-slate-700 select-none min-w-[130px] whitespace-nowrap">PO #</th>
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">MANUFACTURER</th>
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-3 text-center text-slate-700 select-none min-w-[160px] whitespace-nowrap">MODEL / PIXEL PITCH</th>
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-4 text-left text-slate-700 select-none min-w-[300px]">ITEM DESCRIPTION</th>
                            <th rowspan="2" class="col-group col-group-specs py-2.5 px-3 text-center text-slate-700 select-none min-w-[140px] whitespace-nowrap">LOCATION</th>
                            <th colspan="4" class="col-group col-group-inventory py-1 px-3 text-center font-extrabold uppercase text-[11px] sm:text-xs select-none border-b border-yellow-400 whitespace-nowrap bg-yellow-300 text-slate-900 tracking-wider">INVENTORY</th>
                            <th colspan="2" class="col-group col-group-inventory py-1 px-3 text-center font-extrabold uppercase text-[11px] sm:text-xs select-none border-b border-amber-300 whitespace-nowrap bg-amber-200 text-slate-900 tracking-wider">AVAILABLE QTY</th>
                            <th colspan="2" class="col-group col-group-reservation py-1 px-3 text-center font-extrabold uppercase text-[11px] sm:text-xs select-none border-b border-orange-500 whitespace-nowrap bg-orange-500 text-white tracking-wider">RESERVATION</th>
                            <th colspan="2" class="col-group col-group-history py-1 px-3 text-center font-extrabold uppercase text-[11px] sm:text-xs select-none border-b border-red-600 whitespace-nowrap bg-red-600 text-white tracking-wider">HISTORY</th>
                            <th rowspan="2" class="col-group col-group-inventory py-2.5 px-3 text-center font-extrabold uppercase text-[11px] sm:text-xs select-none whitespace-nowrap bg-emerald-600 text-white tracking-wider">ORIGINAL QTY</th>
                            <th colspan="2" class="col-group col-group-history py-1 px-3 text-center font-extrabold uppercase text-[11px] sm:text-xs select-none border-b border-slate-900 whitespace-nowrap bg-slate-900 text-white tracking-wider">STATUS</th>
                            <th rowspan="2" class="py-2.5 px-3 text-center text-slate-700 select-none min-w-[90px] whitespace-nowrap sticky right-0 bg-slate-50">ACTIONS</th>
                        </tr>
                        <tr class="divide-x divide-slate-200 border-b border-slate-200">
                            <!-- INVENTORY SUBHEADERS -->
                            <th class="col-group col-group-inventory py-1.5 px-2.5 text-center text-slate-800 font-bold uppercase text-[10px] tracking-wider select-none min-w-[75px] whitespace-nowrap bg-slate-200">ON-HAND</th>
                            <th class="col-group col-group-inventory py-1.5 px-2.5 text-center text-slate-900 font-extrabold uppercase text-[10px] tracking-wider select-none min-w-[90px] whitespace-nowrap bg-yellow-200">TOTAL ON-HAND</th>
                            <th class="col-group col-group-inventory py-1.5 px-2.5 text-center text-slate-800 font-bold uppercase text-[10px] tracking-wider select-none min-w-[105px] whitespace-nowrap bg-slate-200">PER PANEL SQM</th>
                            <th class="col-group col-group-inventory py-1.5 px-2.5 text-center text-slate-900 font-extrabold uppercase text-[10px] tracking-wider select-none min-w-[125px] whitespace-nowrap bg-yellow-200">TOTAL AVAILABLE SQM</th>
                            <!-- AVAILABLE QTY SUBHEADERS -->
                            <th class="col-group col-group-inventory py-1.5 px-2.5 text-center text-slate-900 font-extrabold uppercase text-[10px] tracking-wider select-none min-w-[75px] whitespace-nowrap bg-amber-100">QTY</th>
                            <th class="col-group col-group-inventory py-1.5 px-2.5 text-center text-slate-900 font-extrabold uppercase text-[10px] tracking-wider select-none min-w-[85px] whitespace-nowrap bg-amber-100">SQM</th>
                            <!-- RESERVATION SUBHEADERS -->
                            <th class="col-group col-group-reservation py-1.5 px-2.5 text-center text-orange-950 font-bold uppercase text-[10px] tracking-wider select-none min-w-[75px] whitespace-nowrap bg-orange-100">QTY</th>
                            <th class="col-group col-group-reservation py-1.5 px-2.5 text-left text-orange-950 font-bold uppercase text-[10px] tracking-wider select-none min-w-[180px] bg-orange-100">REMARKS / PROJECT DETAILS</th>
                            <!-- HISTORY SUBHEADERS -->
                            <th class="col-group col-group-history py-1.5 px-2.5 text-center text-red-950 font-bold uppercase text-[10px] tracking-wider select-none min-w-[75px] whitespace-nowrap bg-red-100">QTY</th>
                            <th class="col-group col-group-history py-1.5 px-2.5 text-left text-red-950 font-bold uppercase text-[10px] tracking-wider select-none min-w-[150px] bg-red-100">PROJECT</th>
                            <!-- STATUS SUBHEADERS -->
                            <th class="col-group col-group-history py-1.5 px-2.5 text-center text-slate-900 font-bold uppercase text-[10px] tracking-wider select-none min-w-[75px] whitespace-nowrap bg-slate-200">QTY</th>
                            <th class="col-group col-group-history py-1.5 px-2.5 text-left text-slate-900 font-bold uppercase text-[10px] tracking-wider select-none min-w-[160px] bg-slate-200">PARTICULAR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-xs sm:text-sm font-medium">
                        @forelse ($items as $item)
                            <tr onclick="openDrawer({{ json_encode($item) }})" class="hover:bg-blue-50/70 transition-colors cursor-pointer group">
                                
                                <!-- TAG # -->
                                <td class="col-group col-group-specs py-3 px-3 font-mono-code font-bold text-center text-slate-800 border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs font-bold text-slate-800">
                                        {{ $item->tag_number ?? 'TAG-LED-'.str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <!-- DATE RECEIVED -->
                                <td class="col-group col-group-specs py-3 px-3 font-mono-code text-center text-slate-600 border-r border-slate-200 whitespace-nowrap">
                                    {{ $item->check_in_date ? $item->check_in_date->format('n/j/Y') : '—' }}
                                </td>

                                <!-- PO # -->
                                <td class="col-group col-group-specs py-3 px-3 font-mono-code text-center text-slate-800 border-r border-slate-200 whitespace-nowrap font-bold">
                                    {{ $item->po_number ?? '—' }}
                                </td>

                                <!-- MANUFACTURER -->
                                <td class="col-group col-group-specs py-3 px-3 font-semibold text-slate-800 uppercase font-mono-code border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->manufacturer }}
                                </td>

                                <!-- MODEL / PIXEL PITCH -->
                                <td class="col-group col-group-specs py-3 px-3 font-mono-code font-semibold text-slate-900 border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->model }}
                                </td>

                                <!-- ITEM DESCRIPTION -->
                                <td class="col-group col-group-specs py-3 px-4 text-slate-800 uppercase font-medium leading-relaxed border-r border-slate-200 min-w-[300px]">
                                    {{ $item->item_description }}
                                </td>

                                <!-- LOCATION -->
                                <td class="col-group col-group-specs py-3 px-3 text-center border-r border-slate-200 font-mono-code whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->location }}
                                    </span>
                                </td>

                                <!-- ON-HAND -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ number_format($item->quantity) }}
                                    </span>
                                </td>

                                <!-- TOTAL ON-HAND -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    @if ($item->acu_quantity !== null)
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-yellow-50 text-amber-900 border border-yellow-200">
                                            {{ number_format($item->acu_quantity) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- PER PANEL SQM -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="font-medium text-xs text-slate-600">
                                        @php
                                            $divisor = $item->acu_quantity ?: $item->quantity;
                                        @endphp
                                        {{ $item->sqm && $divisor > 0 ? number_format($item->sqm / $divisor, 2) : '—' }}
                                    </span>
                                </td>

                                <!-- TOTAL AVAILABLE SQM -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="font-bold text-xs text-slate-900">
                                        {{ $item->sqm !== null ? number_format($item->sqm, 2) : '—' }}
                                    </span>
                                </td>

                                <!-- AVAILABLE QTY: QTY -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-amber-50 text-amber-900 border border-amber-200">
                                        {{ number_format($item->forecasted_quantity !== null ? $item->forecasted_quantity : $item->quantity) }}
                                    </span>
                                </td>

                                <!-- AVAILABLE QTY: SQM -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="font-bold text-xs text-slate-900">
                                        @php
                                            $availQty = $item->forecasted_quantity !== null ? $item->forecasted_quantity : $item->quantity;
                                            $divisor = $item->acu_quantity ?: $item->quantity;
                                            $perPanelSqm = ($item->sqm && $divisor > 0) ? ($item->sqm / $divisor) : 0;
                                            $availSqm = $perPanelSqm > 0 ? ($availQty * $perPanelSqm) : ($item->sqm ?? 0);
                                        @endphp
                                        {{ $availSqm > 0 ? number_format($availSqm, 2) : ($item->sqm !== null ? number_format($item->sqm, 2) : '—') }}
                                    </span>
                                </td>

                                <!-- RESERVATION QTY -->
                                <td class="col-group col-group-reservation py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    @if ($item->reservation_qty !== null && $item->reservation_qty > 0)
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-orange-50 text-orange-900 border border-orange-200">
                                            {{ number_format($item->reservation_qty) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- RESERVATION REMARKS / PROJECT DETAILS -->
                                <td class="col-group col-group-reservation py-3 px-3 text-slate-800 text-xs font-medium border-r border-slate-200 max-w-[220px] truncate" title="{{ $item->reservation_remarks ?: ($item->reservation_project ?: '') }}">
                                    @if ($item->reservation_project || $item->reservation_remarks)
                                        <span class="font-bold text-orange-950">{{ $item->reservation_project }}</span>
                                        @if ($item->reservation_remarks)
                                            <span class="text-slate-500 block text-[11px] truncate">{{ $item->reservation_remarks }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- HISTORY QTY -->
                                <td class="col-group col-group-history py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    @if ($item->history_qty !== null)
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-red-50 text-red-900 border border-red-200">
                                            {{ number_format($item->history_qty) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- HISTORY PROJECT -->
                                <td class="col-group col-group-history py-3 px-3 text-slate-800 text-xs font-medium border-r border-slate-200 max-w-[180px] truncate" title="{{ $item->history_project ?? '' }}">
                                    {{ $item->history_project ?? '—' }}
                                </td>

                                <!-- ORIGINAL QTY -->
                                <td class="col-group col-group-inventory py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-emerald-50 text-emerald-900 border border-emerald-200">
                                        {{ number_format($item->original_quantity ?: $item->quantity) }}
                                    </span>
                                </td>

                                <!-- STATUS QTY -->
                                <td class="col-group col-group-history py-3 px-2 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    @if ($item->status_qty !== null)
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ number_format($item->status_qty) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- STATUS PARTICULAR -->
                                <td class="col-group col-group-history py-3 px-3 text-slate-800 text-xs font-medium border-r border-slate-200 max-w-[200px] truncate" title="{{ $item->status_particular ?: ($item->remarks ?: 'ACTIVE') }}">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->status === 'deficit' ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($item->status === 'reserved' ? 'bg-orange-100 text-orange-800 border border-orange-200' : ($item->status === 'under_repair' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200')) }}">
                                        {{ strtoupper(str_replace('_', ' ', $item->status ?? 'in_stock')) }}
                                    </span>
                                    @if ($item->status_particular || $item->remarks)
                                        <span class="text-slate-600 block text-[11px] truncate mt-0.5">{{ $item->status_particular ?: $item->remarks }}</span>
                                    @endif
                                </td>

                                <!-- ACTIONS -->
                                <td class="py-3 px-3 text-center whitespace-nowrap sticky right-0 bg-white">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openEditModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Edit Item Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openDeleteModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Delete Item Record"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="22" class="py-12 text-center text-slate-500">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-bold text-slate-700">No LED inventory items found</p>
                                        <p class="text-xs text-slate-400">Add an LED module using the button above to populate this category.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                @else
                    <!-- CLEAN SPREADSHEET HEADER FOR EOL PHILIPS UNITS, SERVICE UNITS & ALL -->
                    <thead>
                        <tr class="bg-slate-50 text-slate-700 font-bold uppercase text-[11px] sm:text-xs tracking-wider divide-x divide-slate-200 border-b border-slate-200">
                            @if ($selectedCategory === 'all')
                                <th class="py-2.5 px-3 min-w-[160px] text-center text-slate-700 select-none whitespace-nowrap">CATEGORY</th>
                            @endif
                            <th class="py-2.5 px-4 min-w-[140px] text-center text-slate-700 select-none whitespace-nowrap">MANUFACTURER</th>
                            <th class="py-2.5 px-4 min-w-[120px] text-center text-slate-700 select-none whitespace-nowrap">CHECK IN DATE</th>
                            <th class="py-2.5 px-4 min-w-[150px] text-center text-slate-700 select-none whitespace-nowrap">MODEL</th>
                            <th class="py-2.5 px-4 text-left text-slate-700 select-none min-w-[320px]">ITEM DESCRIPTION</th>
                            <th class="py-2.5 px-4 min-w-[140px] text-center text-slate-700 select-none whitespace-nowrap">LOCATION / BAY</th>
                            <th class="py-2.5 px-4 min-w-[75px] text-center text-slate-700 select-none whitespace-nowrap">QTY</th>
                            @if ($selectedCategory === 'all')
                                <th class="py-2.5 px-3 min-w-[85px] text-center text-slate-700 select-none whitespace-nowrap">SQM</th>
                            @endif
                            <th class="py-2.5 px-4 min-w-[90px] text-center text-slate-700 select-none whitespace-nowrap">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-xs sm:text-sm font-medium">
                        @php
                            $currentSize = null;
                        @endphp

                        @forelse ($items as $item)
                            <!-- Size Section Header (e.g. 24", 31.5", 43", etc.) -->
                            @if ($item->screen_size && $item->screen_size !== $currentSize && !$search && $selectedCategory === 'EOL PHILIPS UNITS')
                                @php
                                    $currentSize = $item->screen_size;
                                @endphp
                                <tr class="bg-slate-50 border-y border-slate-200">
                                    <td colspan="{{ $selectedCategory === 'all' ? 9 : 7 }}" class="py-1 px-4 text-center font-bold text-slate-700 font-mono-code text-xs tracking-wider select-none">
                                        {{ $currentSize }}
                                    </td>
                                </tr>
                            @endif

                            <tr onclick="openDrawer({{ json_encode($item) }})" class="hover:bg-blue-50/70 transition-colors cursor-pointer group">
                                @if ($selectedCategory === 'all')
                                    <td class="py-3 px-3 font-mono-code text-[11px] font-semibold text-slate-700 border-r border-slate-200 text-center whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $item->category }}
                                        </span>
                                    </td>
                                @endif

                                <!-- Manufacturer -->
                                <td class="py-3 px-4 font-semibold text-slate-800 border-r border-slate-200 uppercase font-mono-code text-center whitespace-nowrap">
                                    {{ $item->manufacturer }}
                                </td>

                                <!-- Check-in Date -->
                                <td class="py-3 px-4 font-mono-code text-slate-600 border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->check_in_date ? $item->check_in_date->format('n/j/Y') : '—' }}
                                </td>

                                <!-- Model -->
                                <td class="py-3 px-4 font-mono-code font-semibold text-slate-900 border-r border-slate-200 text-center whitespace-nowrap">
                                    {{ $item->model }}
                                </td>

                                <!-- Item Description -->
                                <td class="py-3 px-4 text-slate-800 uppercase font-medium leading-relaxed border-r border-slate-200 min-w-[320px]">
                                    {{ $item->item_description }}
                                </td>

                                <!-- Location / Bay -->
                                <td class="py-3 px-4 text-center border-r border-slate-200 font-mono-code whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->location }}
                                    </span>
                                </td>

                                <!-- Quantity -->
                                <td class="py-3 px-4 text-center font-mono-code border-r border-slate-200 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[2.25rem] px-2 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $item->quantity }}
                                    </span>
                                </td>

                                @if ($selectedCategory === 'all')
                                    <td class="py-3 px-3 text-center font-mono-code border-r border-slate-200 text-xs whitespace-nowrap">
                                        {{ $item->sqm ? number_format($item->sqm, 2).' m²' : '—' }}
                                    </td>
                                @endif

                                <!-- Action Controls (Edit & Delete) -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openEditModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Edit Item Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openDeleteModal({{ json_encode($item) }})" 
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                            title="Delete Item Record"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectedCategory === 'all' ? 9 : 7 }}" class="py-12 text-center text-slate-500">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-bold text-slate-700">No inventory items found</p>
                                        <p class="text-xs text-slate-400">Add an item using the button above to populate the inventory spreadsheet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>

        <!-- Pagination Footer -->
        @if ($items->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ============================================================= -->
<!-- ============================================================= -->
<!-- MODAL: ADD NEW INVENTORY ITEM                                  -->
<!-- ============================================================= -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeCreateModal()"></div>

    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" id="createModalTitle">Add Inventory Unit</h3>
                    <p class="text-xs text-slate-500" id="createModalSubtitle">Create a new entry in warehouse inventory</p>
                </div>
                <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.inventory.store') }}" class="mt-4 space-y-4">
                @csrf

                <!-- Category Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Inventory Category *</label>
                    <select 
                        id="createCategory" 
                        name="category" 
                        required 
                        onchange="toggleCategoryFields('create')"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    >
                        <optgroup label="Main Inventory">
                            <option value="CENTRALIZED LED INVENTORY">CENTRALIZED LED INVENTORY</option>
                            <option value="EOL PHILIPS UNITS">EOL PHILIPS UNITS</option>
                        </optgroup>
                        <optgroup label="Service Units (Events, Demo)">
                            <option value="LED Service Units" selected>LED Service Units</option>
                            <option value="Philips Service Units">Philips Service Units</option>
                            <option value="Video Controllers / Processors">Video Controllers / Processors</option>
                            <option value="Shuttle">Shuttle</option>
                            <option value="Aver">Aver</option>
                            <option value="Digital iPoster">Digital iPoster</option>
                            <option value="Kiosks">Kiosks</option>
                            <option value="Service Units (Events, Demo)">Service Units (Events, Demo)</option>
                        </optgroup>
                    </select>
                </div>

                <!-- TAG # / CDX and PO / SKU No. -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label id="createTagLabel" class="block text-xs font-bold text-slate-700 mb-1">CDX</label>
                        <input 
                            type="text" 
                            id="createTagInput"
                            name="tag_number" 
                            placeholder="e.g. 1" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                        <p id="createTagHint" class="text-[10px] text-slate-400 mt-1 hidden font-normal">Tip: Separate multiple serial numbers with commas (e.g. SN-001, SN-002)</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">PO / SKU No.</label>
                        <input 
                            type="text" 
                            id="createPoInput"
                            name="po_number" 
                            placeholder="e.g. PO-2026-0104" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Manufacturer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Manufacturer *</label>
                        <select 
                            name="manufacturer" 
                            id="createManufacturer"
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                            <option value="">-- Select Manufacturer --</option>
                            <option value="UNILUMIN">UNILUMIN</option>
                            <option value="FABULUX">FABULUX</option>
                            <option value="LKGT - INDOOR LED DISPLAY">LKGT - INDOOR LED DISPLAY</option>
                            <option value="DAHUA">DAHUA</option>
                            <option value="LEDTOP">LEDTOP</option>
                            <option value="ABSEN">ABSEN</option>
                            <option value="UNIVIEW">UNIVIEW</option>
                            <option value="LIGHTKING">LIGHTKING</option>
                            <option value="LEYARD">LEYARD</option>
                            <option value="DAHUA TECH">DAHUA TECH</option>
                            <option value="LINSO">LINSO</option>
                            <option value="SHANGHAI">SHANGHAI</option>
                            <option value="SHENZEN">SHENZEN</option>
                            <option value="SAMSUNG">SAMSUNG</option>
                            <option value="TRT">TRT</option>
                            <option value="GLOBALTRONICS">GLOBALTRONICS</option>
                            <option value="PHILIPS">PHILIPS</option>
                        </select>
                    </div>

                    <!-- Date Received / Check-in Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Check In Date *</label>
                        <input 
                            type="date" 
                            name="check_in_date" 
                            value="{{ date('Y-m-d') }}" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Model / Pixel Pitch -->
                    <div>
                        <label id="createModelLabel" class="block text-xs font-bold text-slate-700 mb-1">Model *</label>
                        <input 
                            type="text" 
                            id="createModelInput"
                            name="model" 
                            required 
                            placeholder="e.g. P2.5 INDOOR" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- Screen Size (for monitors) -->
                    <div id="createScreenSizeGroup" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Screen Size (e.g. 55")</label>
                        <input 
                            type="text" 
                            id="createScreenSizeInput"
                            name="screen_size" 
                            placeholder="e.g. 55\"" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Location / Bay Dropdown (Globaltronics, Marikina, Ajuan, 2nd Flr Ocap) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Location *</label>
                        <select 
                            name="location" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                            <option value="">-- Select Location --</option>
                            <option value="GLOBALTRONICS" selected>GLOBALTRONICS</option>
                            <option value="MARIKINA">MARIKINA</option>
                            <option value="2ND FLR OCAP">2ND FLR OCAP</option>
                            <option value="AJUAN - 1ST FLR">AJUAN - 1ST FLR</option>
                            <option value="A JUAN MAIN 1ST FLOOR">A JUAN MAIN 1ST FLOOR</option>
                            <option value="Globaltronics">Globaltronics (Legacy)</option>
                            <option value="AJUAN">AJUAN (Legacy)</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Quantity (QTY) *</label>
                        <input 
                            type="number" 
                            id="createQuantity"
                            name="quantity" 
                            min="0" 
                            value="1" 
                            required 
                            oninput="updateInventoryCalculation('create')"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- Acu. QTY (for Philips Service Units) -->
                    <div id="createAcuQtyGroup" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Acu. QTY</label>
                        <input 
                            type="number" 
                            id="createAcuQty"
                            name="acu_quantity" 
                            min="0" 
                            placeholder="e.g. 6" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- Available QTY (for Philips Service Units) -->
                    <div id="createForecastedQtyGroup" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Available QTY</label>
                        <input 
                            type="number" 
                            id="createForecastedQty"
                            name="forecasted_quantity" 
                            min="0" 
                            placeholder="e.g. 5" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- SQM (Square Meters) -->
                    <div id="createSqmGroup">
                        <label id="createSqmLabel" class="block text-xs font-bold text-slate-700 mb-1">Total Available SQM (m²)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="sqm" 
                            id="createSqmInput"
                            placeholder="e.g. 12.00" 
                            oninput="updateInventoryCalculation('create')"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <!-- Live Inventory Breakdown Card for LED Service Units -->
                <div id="createInventoryBreakdown" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider font-mono-code">Inventory Metrics Breakdown</span>
                        <span class="text-[10px] text-blue-600 font-mono-code font-semibold">Auto-Calculated</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center text-xs font-mono-code">
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">QTY</span>
                            <span id="createCalcQty" class="font-bold text-slate-900">1</span>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">ACU. QTY</span>
                            <span id="createCalcAcuQty" class="font-bold text-slate-900">1</span>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">PER PANEL SQM</span>
                            <span id="createCalcPerPanel" class="font-bold text-blue-600">—</span>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">TOTAL AVAILABLE SQM</span>
                            <span id="createCalcTotalSqm" class="font-bold text-slate-900">—</span>
                        </div>
                    </div>
                </div>

                <!-- Status & Original Quantity -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Badge</label>
                        <select 
                            name="status" 
                            id="createStatus"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                            <option value="in_stock" selected>ACTIVE / IN STOCK</option>
                            <option value="low_stock">LOW STOCK</option>
                            <option value="reserved">RESERVED</option>
                            <option value="deficit">DEFICIT</option>
                            <option value="under_repair">UNDER REPAIR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Original Received Qty</label>
                        <input 
                            type="number" 
                            id="createOriginalQty" 
                            name="original_quantity" 
                            min="0" 
                            placeholder="Defaults to QTY if empty"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Operational Remarks</label>
                    <input 
                        type="text" 
                        name="remarks" 
                        id="createRemarks"
                        placeholder="e.g. New shipment in good condition" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    >
                </div>

                <!-- Item Description / Particular -->
                <div>
                    <label id="createDescriptionLabel" class="block text-xs font-bold text-slate-700 mb-1">Item Description *</label>
                    <textarea 
                        id="createDescriptionInput"
                        name="item_description" 
                        rows="3" 
                        required 
                        placeholder="e.g. 500x500mm Die-Cast Aluminum Cabinet, High Refresh Rate, Front Serviceable Demo Unit" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        onclick="closeCreateModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20"
                    >
                        Save Inventory Unit
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDIT INVENTORY ITEM                                     -->
<!-- ============================================================= -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>

    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Edit Inventory Item</h3>
                    <p class="text-xs text-slate-500" id="editModalSubtitle">Update unit specifications or quantity</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Inventory Category *</label>
                    <select 
                        id="editCategory" 
                        name="category" 
                        required 
                        onchange="toggleCategoryFields('edit')"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    >
                        <optgroup label="Main Inventory">
                            <option value="CENTRALIZED LED INVENTORY">CENTRALIZED LED INVENTORY</option>
                            <option value="EOL PHILIPS UNITS">EOL PHILIPS UNITS</option>
                        </optgroup>
                        <optgroup label="Service Units (Events, Demo)">
                            <option value="LED Service Units">LED Service Units</option>
                            <option value="Philips Service Units">Philips Service Units</option>
                            <option value="Video Controllers / Processors">Video Controllers / Processors</option>
                            <option value="Shuttle">Shuttle</option>
                            <option value="Aver">Aver</option>
                            <option value="Digital iPoster">Digital iPoster</option>
                            <option value="Kiosks">Kiosks</option>
                            <option value="Service Units (Events, Demo)">Service Units (Events, Demo)</option>
                        </optgroup>
                    </select>
                </div>

                <!-- TAG # / CDX and PO / SKU No. -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label id="editTagLabel" class="block text-xs font-bold text-slate-700 mb-1">CDX</label>
                        <input 
                            type="text" 
                            id="editTagNumber" 
                            name="tag_number" 
                            placeholder="e.g. 1" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                        <p id="editTagHint" class="text-[10px] text-slate-400 mt-1 hidden font-normal">Tip: Separate multiple serial numbers with commas (e.g. SN-001, SN-002)</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">PO / SKU No.</label>
                        <input 
                            type="text" 
                            id="editPoNumber" 
                            name="po_number" 
                            placeholder="e.g. PO-2026-0104" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Manufacturer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Manufacturer *</label>
                        <select 
                            id="editManufacturer" 
                            name="manufacturer" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                            <option value="">-- Select Manufacturer --</option>
                            <option value="UNILUMIN">UNILUMIN</option>
                            <option value="FABULUX">FABULUX</option>
                            <option value="LKGT - INDOOR LED DISPLAY">LKGT - INDOOR LED DISPLAY</option>
                            <option value="DAHUA">DAHUA</option>
                            <option value="LEDTOP">LEDTOP</option>
                            <option value="ABSEN">ABSEN</option>
                            <option value="UNIVIEW">UNIVIEW</option>
                            <option value="LIGHTKING">LIGHTKING</option>
                            <option value="LEYARD">LEYARD</option>
                            <option value="DAHUA TECH">DAHUA TECH</option>
                            <option value="LINSO">LINSO</option>
                            <option value="SHANGHAI">SHANGHAI</option>
                            <option value="SHENZEN">SHENZEN</option>
                            <option value="SAMSUNG">SAMSUNG</option>
                            <option value="TRT">TRT</option>
                            <option value="GLOBALTRONICS">GLOBALTRONICS</option>
                            <option value="PHILIPS">PHILIPS</option>
                        </select>
                    </div>

                    <!-- Date Received / Check-in Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Check In Date *</label>
                        <input 
                            type="date" 
                            id="editCheckInDate" 
                            name="check_in_date" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Model / Pixel Pitch -->
                    <div>
                        <label id="editModelLabel" class="block text-xs font-bold text-slate-700 mb-1">Model *</label>
                        <input 
                            type="text" 
                            id="editModel" 
                            name="model" 
                            required 
                            placeholder="e.g. P2.5 INDOOR" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- Screen Size (for monitors) -->
                    <div id="editScreenSizeGroup" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Screen Size (e.g. 55")</label>
                        <input 
                            type="text" 
                            id="editScreenSize" 
                            name="screen_size" 
                            placeholder="e.g. 55\"" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Location / Bay Dropdown (Globaltronics, Marikina, Ajuan, 2nd Flr Ocap) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Location *</label>
                        <select 
                            id="editLocation" 
                            name="location" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                            <option value="">-- Select Location --</option>
                            <option value="GLOBALTRONICS">GLOBALTRONICS</option>
                            <option value="MARIKINA">MARIKINA</option>
                            <option value="2ND FLR OCAP">2ND FLR OCAP</option>
                            <option value="AJUAN - 1ST FLR">AJUAN - 1ST FLR</option>
                            <option value="A JUAN MAIN 1ST FLOOR">A JUAN MAIN 1ST FLOOR</option>
                            <option value="Globaltronics">Globaltronics (Legacy)</option>
                            <option value="AJUAN">AJUAN (Legacy)</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Quantity (QTY) *</label>
                        <input 
                            type="number" 
                            id="editQuantity" 
                            name="quantity" 
                            min="0" 
                            required 
                            oninput="updateInventoryCalculation('edit')"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- Acu. QTY (for Philips Service Units) -->
                    <div id="editAcuQtyGroup" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Acu. QTY</label>
                        <input 
                            type="number" 
                            id="editAcuQty" 
                            name="acu_quantity" 
                            min="0" 
                            placeholder="e.g. 6" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- Available QTY (for Philips Service Units) -->
                    <div id="editForecastedQtyGroup" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Available QTY</label>
                        <input 
                            type="number" 
                            id="editForecastedQty" 
                            name="forecasted_quantity" 
                            min="0" 
                            placeholder="e.g. 5" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>

                    <!-- SQM -->
                    <div id="editSqmGroup">
                        <label id="editSqmLabel" class="block text-xs font-bold text-slate-700 mb-1">Total Available SQM (m²)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            id="editSqm" 
                            name="sqm" 
                            placeholder="e.g. 12.00" 
                            oninput="updateInventoryCalculation('edit')"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <!-- Live Inventory Breakdown Card for LED Service Units (Edit Modal) -->
                <div id="editInventoryBreakdown" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider font-mono-code">Inventory Metrics Breakdown</span>
                        <span class="text-[10px] text-blue-600 font-mono-code font-semibold">Auto-Calculated</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center text-xs font-mono-code">
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">QTY</span>
                            <span id="editCalcQty" class="font-bold text-slate-900">0</span>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">ACU. QTY</span>
                            <span id="editCalcAcuQty" class="font-bold text-slate-900">0</span>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">PER PANEL SQM</span>
                            <span id="editCalcPerPanel" class="font-bold text-blue-600">—</span>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-500 block font-bold">TOTAL AVAILABLE SQM</span>
                            <span id="editCalcTotalSqm" class="font-bold text-slate-900">—</span>
                        </div>
                    </div>
                </div>

                <!-- Status & Original Quantity -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Badge</label>
                        <select 
                            name="status" 
                            id="editStatus"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                            <option value="in_stock">ACTIVE / IN STOCK</option>
                            <option value="low_stock">LOW STOCK</option>
                            <option value="reserved">RESERVED</option>
                            <option value="deficit">DEFICIT</option>
                            <option value="under_repair">UNDER REPAIR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Original Received Qty</label>
                        <input 
                            type="number" 
                            id="editOriginalQty" 
                            name="original_quantity" 
                            min="0" 
                            placeholder="e.g. 500"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono-code font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        >
                    </div>
                </div>

                <!-- Operational Remarks -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Operational Remarks</label>
                    <input 
                        type="text" 
                        name="remarks" 
                        id="editRemarks"
                        placeholder="e.g. New shipment in good condition" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    >
                </div>

                <!-- Item Description / Particular -->
                <div>
                    <label id="editDescriptionLabel" class="block text-xs font-bold text-slate-700 mb-1">Item Description *</label>
                    <textarea 
                        id="editDescription" 
                        name="item_description" 
                        rows="3" 
                        required 
                        placeholder="e.g. 500x500mm Die-Cast Aluminum Cabinet, High Refresh Rate" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        onclick="closeEditModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20"
                    >
                        Update Inventory Item
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: DELETE INVENTORY ITEM                                   -->
<!-- ============================================================= -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>

    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 p-6 z-10 animate-fadeIn">
            
            <div class="flex items-center gap-3 text-rose-600 mb-3">
                <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Confirm Deletion</h3>
                    <p class="text-xs text-slate-500">This action will remove the item from inventory.</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Are you sure you want to delete unit <strong id="deleteModalModel" class="text-slate-900 font-mono-code font-bold"></strong>? This record will be permanently purged from warehouse stock records.
            </p>

            <form id="deleteForm" method="POST" action="" class="mt-5 flex items-center justify-end gap-2.5">
                @csrf
                @method('DELETE')

                <button 
                    type="button" 
                    onclick="closeDeleteModal()" 
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/20"
                >
                    Delete Item
                </button>
            </form>

        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- LUMINA-SPEC STYLES & OVERRIDES FOR SOLID OPAQUE DRAWER        -->
<!-- ============================================================= -->
<style>
    #itemDrawer {
        position: fixed !important;
        top: 0 !important;
        bottom: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 32rem !important;
        background-color: #0b0f17 !important;
        background: #0b0f17 !important;
        color: #f1f5f9 !important;
        z-index: 999999 !important;
        box-shadow: -20px 0 50px rgba(0, 0, 0, 0.95) !important;
        border-left: 1px solid #1e293b !important;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        opacity: 1 !important;
    }
    #itemDrawerBackdrop {
        position: fixed !important;
        inset: 0 !important;
        background-color: rgba(0, 0, 0, 0.75) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        z-index: 999990 !important;
        transition: opacity 0.3s ease-in-out !important;
    }
    #reserveModal {
        z-index: 9999999 !important;
    }
</style>

<!-- ============================================================= -->
<!-- BACKDROP FOR SLIDE-OVER DRAWER                                -->
<!-- ============================================================= -->
<div id="itemDrawerBackdrop" class="fixed inset-0 hidden opacity-0" onclick="closeDrawer()" style="background-color: rgba(0, 0, 0, 0.75) !important; z-index: 999990 !important; backdrop-filter: blur(4px) !important;"></div>

<!-- ============================================================= -->
<!-- SIDE DRAWER DETAILS & HISTORY (LuminaSpec Reference Design)   -->
<!-- ============================================================= -->
<div id="itemDrawer" class="fixed inset-y-0 right-0 w-full max-w-lg shadow-2xl transform translate-x-full flex flex-col" style="background-color: #0b0f17 !important; color: #f1f5f9 !important; z-index: 999999 !important; border-left: 1px solid #1e293b !important;">
    
    <!-- Drawer Header -->
    <div class="p-6 border-b flex justify-between items-center" style="background-color: #0d131f !important; border-color: #1e293b !important;">
        <div>
            <span id="drawerTag" class="text-[10px] font-bold px-2.5 py-0.5 rounded uppercase tracking-wider" style="background-color: rgba(14, 165, 233, 0.2) !important; color: #38bdf8 !important; border: 1px solid rgba(14, 165, 233, 0.3) !important;">
                SKU DETAILS
            </span>
            <h2 id="drawerSku" class="text-xl font-bold text-white mt-1 font-mono tracking-wide">
                AUN2F3362A
            </h2>
            <h2 id="drawerTitle" class="hidden">AUN2F3362A</h2>
        </div>
        <button type="button" onclick="closeDrawer()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Drawer Body -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6" style="background-color: #0b0f17 !important;">
        
        <!-- Item Specifications -->
        <div class="p-4 rounded-xl space-y-3" style="background-color: #151c28 !important; border: 1px solid #1e293b !important;">
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-slate-400 text-[11px] font-medium uppercase tracking-wider">Manufacturer</p>
                    <p id="drawerBrand" class="font-bold text-slate-100 uppercase mt-0.5 text-sm">UNILUMIN</p>
                    <span id="drawerMfg" class="hidden">UNILUMIN</span>
                </div>
                <div>
                    <p class="text-slate-400 text-[11px] font-medium uppercase tracking-wider">Model / Pitch</p>
                    <p id="drawerModel" class="font-bold text-slate-100 font-mono mt-0.5 text-sm">UMINI P1.2 COB</p>
                </div>
                <div class="col-span-2 pt-2.5" style="border-top: 1px solid rgba(255,255,255,0.08) !important;">
                    <p class="text-slate-400 text-[11px] font-medium uppercase tracking-wider">Description</p>
                    <p id="drawerDesc" class="font-medium text-slate-200 text-xs mt-0.5 leading-relaxed uppercase">LED DISPLAY (600 X 337.5MM) - INDOOR</p>
                </div>
                <div class="col-span-2 pt-2.5 flex justify-between items-center" style="border-top: 1px solid rgba(255,255,255,0.08) !important;">
                    <div>
                        <p class="text-slate-400 text-[11px] font-medium uppercase tracking-wider">Location Warehouse</p>
                        <p id="drawerLocation" class="font-bold text-amber-400 text-xs mt-0.5 uppercase">MARIKINA</p>
                    </div>
                    <div class="text-right">
                        <p class="text-slate-400 text-[11px] font-medium uppercase tracking-wider">Date Received</p>
                        <p id="drawerDate" class="font-mono font-medium text-slate-200 text-xs mt-0.5">10/15/2025</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Remarks Box -->
        <div class="p-4 rounded-xl space-y-2" style="background-color: #111724 !important; border: 1px solid #1e293b !important;">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Item Status & Remarks</span>
                <span id="drawerStatusBadge" class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    ACTIVE
                </span>
            </div>
            <p id="drawerRemarks" class="text-xs text-slate-300 italic leading-relaxed">
                No special remarks noted.
            </p>
        </div>

        <!-- Inventory Metrics Breakdown -->
        <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">
                Inventory Quantities
            </h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3 rounded-xl text-center" style="background-color: #111724 !important; border: 1px solid #1e293b !important;">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Original Qty</span>
                    <p id="drawerOriginalQty" class="text-base font-extrabold text-slate-200 mt-1">500 pcs</p>
                </div>
                <div class="p-3 rounded-xl text-center" style="background-color: #111724 !important; border: 1px solid #1e293b !important;">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">On-Hand Qty</span>
                    <p id="drawerOnHand" class="text-base font-extrabold text-white mt-1">500 pcs</p>
                    <span id="drawerOnHandQty" class="hidden">500 pcs</span>
                </div>
                <div id="drawerAvailBox" class="p-3 rounded-xl text-center" style="background-color: rgba(6, 78, 59, 0.3) !important; border: 1px solid rgba(16, 185, 129, 0.3) !important;">
                    <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider block">Available</span>
                    <p id="drawerAvailQty" class="text-base font-extrabold text-emerald-400 mt-1">500 pcs</p>
                    <span id="drawerAvailableQty" class="hidden">500 pcs</span>
                </div>
            </div>
        </div>

        <!-- Detailed Project Reservations -->
        <div>
            <div class="flex justify-between items-center mb-2.5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Project Reservations</h3>
                <button type="button" onclick="openReserveForCurrent()" class="text-xs text-amber-400 hover:text-amber-300 font-bold hover:underline transition-colors">
                    + New Reservation
                </button>
            </div>
            <div id="drawerReservations" class="space-y-2">
                <p class="text-xs text-slate-500 italic py-2">No active project allocations.</p>
            </div>
            <div id="drawerReservationsList" class="hidden"></div>
        </div>

        <!-- Transaction & Movement History -->
        <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">Movement History</h3>
            <div id="drawerHistory" class="space-y-2 border-l-2 ml-2 pl-4" style="border-color: #1e293b !important;">
                <div class="relative pb-3 text-xs">
                    <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-cyan-400 ring-2 ring-slate-900"></div>
                    <div class="text-[10px] text-slate-500 font-mono">2025-10-15 09:30</div>
                    <div class="font-semibold text-slate-300 mt-0.5">RECEIVED: Initial stock received</div>
                </div>
            </div>
            <div id="drawerMovementList" class="hidden"></div>
        </div>

    </div>

    <!-- Drawer Footer Actions -->
    <div class="p-4 border-t flex gap-2.5" style="background-color: #0d131f !important; border-color: #1e293b !important;">
        <button type="button" onclick="editCurrentRow()" class="flex-1 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-1.5" style="background-color: #1e293b !important; border: 1px solid #334155 !important;">
            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Details
        </button>
        <button type="button" onclick="closeDrawer()" class="px-5 text-slate-300 hover:text-white text-xs font-semibold py-2.5 rounded-xl transition" style="background-color: #151c28 !important; border: 1px solid #1e293b !important;">
            Close
        </button>
    </div>

</div>

<!-- ============================================================= -->
<!-- EXTENDED RESERVATION MODAL (LuminaSpec Reference Design)      -->
<!-- ============================================================= -->
<div id="reserveModal" class="fixed inset-0 hidden flex items-center justify-center p-4 overflow-y-auto" style="z-index: 9999999 !important; background-color: rgba(0, 0, 0, 0.8) !important; backdrop-filter: blur(4px) !important;">
    <div class="rounded-2xl max-w-lg w-full p-6 shadow-2xl relative my-8" style="background-color: #0f172a !important; border: 1px solid #1e293b !important; color: #f1f5f9 !important;">
        <div class="flex justify-between items-center border-b pb-4 mb-4" style="border-color: #1e293b !important;">
            <h3 class="font-bold text-amber-400 text-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                </svg>
                Create Reservation & Allocation
            </h3>
            <button type="button" onclick="closeModal('reserveModal')" class="text-slate-400 hover:text-white p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="reserveForm" method="POST" action="" onsubmit="handleReserveStock(event)" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Select LED Display SKU *</label>
                <select id="reserveSkuSelect" required onchange="onReserveSkuChange(this.value)" class="w-full rounded-xl p-2.5 text-xs text-white focus:outline-none" style="background-color: #0b111e !important; color: #ffffff !important; border: 1px solid #334155 !important;">
                    @foreach ($items as $itm)
                        <option value="{{ $itm->id }}" data-avail="{{ $itm->forecasted_quantity !== null ? $itm->forecasted_quantity : $itm->quantity }}">
                            {{ $itm->po_number ?? ($itm->tag_number ?? 'SKU-'.$itm->id) }} — {{ $itm->manufacturer }} {{ $itm->model }} ({{ $itm->location }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Project / Client Name *</label>
                    <input type="text" name="reservation_project" id="reserveProject" required placeholder="e.g. Globe Telco Event" class="w-full rounded-xl p-2.5 text-xs text-white focus:outline-none" style="background-color: #0b111e !important; color: #ffffff !important; border: 1px solid #334155 !important;">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Reserved Quantity (PCS) *</label>
                    <input type="number" name="reservation_qty" id="reserveQty" required min="1" value="10" class="w-full rounded-xl p-2.5 text-xs text-white focus:outline-none" style="background-color: #0b111e !important; color: #ffffff !important; border: 1px solid #334155 !important;">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Project Details / Location</label>
                <input type="text" id="reserveProjectDetails" placeholder="e.g. SMX Convention Hall B Main Stage" class="w-full rounded-xl p-2.5 text-xs text-white focus:outline-none" style="background-color: #0b111e !important; color: #ffffff !important; border: 1px solid #334155 !important;">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Particulars / Special Specs</label>
                <textarea name="reservation_remarks" id="reserveParticulars" rows="2" placeholder="e.g. Requires 5x3 setup with hanging bars and power link cables..." class="w-full rounded-xl p-2 text-xs text-white focus:outline-none" style="background-color: #0b111e !important; color: #ffffff !important; border: 1px solid #334155 !important;"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t" style="border-color: #1e293b !important;">
                <button type="button" onclick="closeModal('reserveModal')" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white">Cancel</button>
                <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white font-bold px-5 py-2.5 rounded-xl text-xs transition shadow-md shadow-amber-600/30">Confirm Allocation</button>
            </div>
        </form>
    </div>
</div>

<script>
    let activeDrawerItemId = null;
    let currentDrawerItem = null;

    // Column View Mode Toggle (LuminaSpec Reference)
    function setColumnView(viewMode) {
        const groups = ['specs', 'inventory', 'reservation', 'history'];
        const buttons = {
            all: 'btnViewAll',
            specs: 'btnViewSpecs',
            inventory: 'btnViewInventory',
            reservation: 'btnViewReservation',
            history: 'btnViewHistory'
        };

        Object.keys(buttons).forEach(key => {
            const btn = document.getElementById(buttons[key]);
            if (!btn) return;
            if (key === viewMode) {
                btn.className = "px-3 py-1.5 rounded-xl text-xs font-semibold text-white transition whitespace-nowrap shadow-sm";
                btn.style.backgroundColor = "#0284c7";
                btn.style.color = "#ffffff";
            } else {
                btn.className = "px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition whitespace-nowrap";
                btn.style.backgroundColor = "";
                btn.style.color = "";
            }
        });

        groups.forEach(group => {
            const elements = document.querySelectorAll(`.col-group-${group}`);
            elements.forEach(el => {
                if (viewMode === 'all' || viewMode === group) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        });
    }

    // Open Slide Drawer (LuminaSpec Reference Design)
    function openDrawer(item) {
        if (!item) return;
        currentDrawerItem = item;
        activeDrawerItemId = item.id;

        const sku = item.po_number || item.tag_number || item.model || ('SKU-' + item.id);
        const tag = item.tag_number || '';
        const mfg = (item.manufacturer || 'UNILUMIN').toUpperCase();
        const model = item.model || '—';
        const desc = item.item_description || '—';
        const loc = (item.location || 'MARIKINA').toUpperCase();
        const date = item.check_in_date ? String(item.check_in_date).substring(0, 10) : '—';

        // Tag and SKU
        const tagEl = document.getElementById('drawerTag');
        if (tagEl) tagEl.textContent = tag ? `TAG: ${tag}` : 'SKU DETAILS';

        const skuEl = document.getElementById('drawerSku');
        if (skuEl) skuEl.textContent = sku;
        const legacyTitle = document.getElementById('drawerTitle');
        if (legacyTitle) legacyTitle.textContent = sku;

        // Specs
        const brandEl = document.getElementById('drawerBrand');
        if (brandEl) brandEl.textContent = mfg;
        const legacyMfg = document.getElementById('drawerMfg');
        if (legacyMfg) legacyMfg.textContent = mfg;

        const modelEl = document.getElementById('drawerModel');
        if (modelEl) modelEl.textContent = model;

        const descEl = document.getElementById('drawerDesc');
        if (descEl) descEl.textContent = desc;

        const locEl = document.getElementById('drawerLocation');
        if (locEl) locEl.textContent = loc;

        const dateEl = document.getElementById('drawerDate');
        if (dateEl) dateEl.textContent = date;

        // Quantities & Deficit Calculations
        const origQty = (item.original_quantity !== null && item.original_quantity !== undefined) ? Number(item.original_quantity) : Number(item.quantity || 0);
        const onHandQty = Number(item.quantity || 0);
        const reservedQty = Number(item.reservation_qty || 0);
        const availQty = (item.forecasted_quantity !== null && item.forecasted_quantity !== undefined) ? Number(item.forecasted_quantity) : (onHandQty - reservedQty);
        const isDeficit = (availQty < 0) || (item.status === 'deficit');

        const origQtyEl = document.getElementById('drawerOriginalQty');
        if (origQtyEl) origQtyEl.textContent = `${origQty} pcs`;

        const onHandEl = document.getElementById('drawerOnHand');
        if (onHandEl) onHandEl.textContent = `${onHandQty} pcs`;
        const legacyOnHand = document.getElementById('drawerOnHandQty');
        if (legacyOnHand) legacyOnHand.textContent = `${onHandQty} pcs`;

        const availQtyEl = document.getElementById('drawerAvailQty');
        if (availQtyEl) availQtyEl.textContent = `${availQty} pcs`;
        const legacyAvail = document.getElementById('drawerAvailableQty');
        if (legacyAvail) legacyAvail.textContent = `${availQty} pcs`;

        const availBox = document.getElementById('drawerAvailBox');
        if (availBox) {
            if (isDeficit) {
                availBox.style.backgroundColor = "rgba(136, 19, 55, 0.4) !important";
                availBox.style.borderColor = "rgba(244, 63, 94, 0.4) !important";
                if (availQtyEl) availQtyEl.className = "text-base font-extrabold text-rose-400 mt-1";
            } else {
                availBox.style.backgroundColor = "rgba(6, 78, 59, 0.3) !important";
                availBox.style.borderColor = "rgba(16, 185, 129, 0.3) !important";
                if (availQtyEl) availQtyEl.className = "text-base font-extrabold text-emerald-400 mt-1";
            }
        }

        // Status & Remarks
        const statusBadge = document.getElementById('drawerStatusBadge');
        if (statusBadge) {
            if (isDeficit) {
                statusBadge.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30 animate-pulse";
                statusBadge.textContent = "DEFICIT";
            } else if (item.status === 'under_repair' || item.status === 'MAINTENANCE') {
                statusBadge.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30";
                statusBadge.textContent = "MAINTENANCE";
            } else if (reservedQty > 0 || item.status === 'reserved') {
                statusBadge.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-sky-500/20 text-sky-400 border border-sky-500/30";
                statusBadge.textContent = "RESERVED";
            } else {
                statusBadge.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30";
                statusBadge.textContent = "ACTIVE";
            }
        }

        const remarksEl = document.getElementById('drawerRemarks');
        if (remarksEl) {
            remarksEl.textContent = item.remarks || item.status_particular || 'No special remarks noted.';
        }

        // Project Reservations
        const resContainer = document.getElementById('drawerReservations');
        if (resContainer) {
            if (reservedQty > 0 || item.reservation_project) {
                resContainer.innerHTML = `
                    <div class="p-3.5 rounded-xl text-xs space-y-1.5" style="background-color: #111724 !important; border: 1px solid #1e293b !important;">
                        <div class="flex justify-between items-start">
                            <span class="font-bold text-slate-100 text-sm">${item.reservation_project || 'Project Allocation'}</span>
                            <span class="text-amber-400 font-bold bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">${reservedQty} pcs reserved</span>
                        </div>
                        ${item.reservation_remarks ? `<div class="text-slate-300 text-xs italic"><span class="text-slate-500">Details:</span> ${item.reservation_remarks}</div>` : ''}
                    </div>
                `;
            } else {
                resContainer.innerHTML = `<p class="text-xs text-slate-500 italic py-2">No active project allocations.</p>`;
            }
        }

        // Movement History
        const historyContainer = document.getElementById('drawerHistory');
        if (historyContainer) {
            let historyList = [];
            if (Array.isArray(item.movement_history) && item.movement_history.length > 0) {
                historyList = item.movement_history;
            } else {
                historyList = [
                    {
                        date: (date !== '—' ? date : '2025-10-15') + ' 09:30',
                        desc: `Received initial stock of ${origQty} pcs`
                    }
                ];
            }

            historyContainer.innerHTML = historyList.slice().reverse().map(h => {
                const hDate = h.date || '—';
                const hText = h.desc || h.action || (h.type ? `${h.type}: ${h.desc}` : 'Movement logged');
                return `
                    <div class="relative pb-3 text-xs">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-cyan-400 ring-2 ring-slate-900"></div>
                        <div class="text-[10px] text-slate-500 font-mono">${hDate}</div>
                        <div class="font-semibold text-slate-300 mt-0.5">${hText}</div>
                    </div>
                `;
            }).join('');
        }

        // Show Drawer & Backdrop with animation
        const backdrop = document.getElementById('itemDrawerBackdrop');
        const drawer = document.getElementById('itemDrawer');
        if (backdrop) {
            backdrop.classList.remove('hidden');
            requestAnimationFrame(() => {
                backdrop.style.opacity = '1';
            });
        }
        if (drawer) {
            drawer.classList.remove('translate-x-full');
            drawer.style.transform = 'translateX(0)';
        }
    }

    // Close Slide Drawer
    function closeDrawer() {
        const backdrop = document.getElementById('itemDrawerBackdrop');
        const drawer = document.getElementById('itemDrawer');
        if (drawer) {
            drawer.classList.add('translate-x-full');
            drawer.style.transform = 'translateX(100%)';
        }
        if (backdrop) {
            backdrop.style.opacity = '0';
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
        activeDrawerItemId = null;
    }

    // Edit Item Details from Drawer
    function editCurrentRow() {
        if (!currentDrawerItem) return;
        const itemToEdit = currentDrawerItem;
        closeDrawer();
        setTimeout(() => {
            openEditModal(itemToEdit);
        }, 320);
    }

    // Reservation Modal Controls
    function openReserveForCurrent() {
        if (!currentDrawerItem) return;
        const select = document.getElementById('reserveSkuSelect');
        if (select) {
            select.value = currentDrawerItem.id;
            onReserveSkuChange(currentDrawerItem.id);
        }
        openModal('reserveModal');
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        if (modalId === 'reserveModal' && currentDrawerItem) {
            const select = document.getElementById('reserveSkuSelect');
            if (select) {
                select.value = currentDrawerItem.id;
                onReserveSkuChange(currentDrawerItem.id);
            }
        }
        modal.classList.remove('hidden');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    }

    function onReserveSkuChange(skuId) {
        const form = document.getElementById('reserveForm');
        if (form && skuId) {
            form.action = '/admin/inventory/' + skuId + '/reserve';
        }
    }

    function handleReserveStock(e) {
        const select = document.getElementById('reserveSkuSelect');
        if (!select || !select.value) return;
        const form = document.getElementById('reserveForm');
        form.action = '/admin/inventory/' + select.value + '/reserve';

        const details = (document.getElementById('reserveProjectDetails')?.value || '').trim();
        const particulars = (document.getElementById('reserveParticulars')?.value || '').trim();
        
        let combinedRemarks = particulars;
        if (details) {
            combinedRemarks = details + (particulars ? ' | ' + particulars : '');
        }
        const remarksField = document.getElementById('reserveParticulars');
        if (remarksField) remarksField.value = combinedRemarks;
    }

    // Aliases to support all calls
    const openItemDrawer = openDrawer;
    const closeItemDrawer = closeDrawer;
    const editCurrentDrawerItem = editCurrentRow;
    const openReservationModalFromDrawer = openReserveForCurrent;

    // Create Modal & Category Field Toggles
    function openCreateModal(defaultCategory) {
        if (defaultCategory) {
            const selectEl = document.getElementById('createCategory');
            if (selectEl) selectEl.value = defaultCategory;
        }
        toggleCategoryFields('create');
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function toggleCategoryFields(mode) {
        const catSelect = document.getElementById(mode + 'Category');
        if (!catSelect) return;
        const cat = catSelect.value;
        const mfg = document.getElementById(mode + 'Manufacturer');
        const modalTitle = document.getElementById(mode + 'ModalTitle');
        const tagLabel = document.getElementById(mode + 'TagLabel');
        const tagInput = document.getElementById(mode === 'create' ? 'createTagInput' : 'editTagNumber');
        const tagHint = document.getElementById(mode + 'TagHint');
        const modelLabel = document.getElementById(mode + 'ModelLabel');
        const modelInput = document.getElementById(mode === 'create' ? 'createModelInput' : 'editModel');
        const poInput = document.getElementById(mode === 'create' ? 'createPoInput' : 'editPoNumber');
        const screenSizeGroup = document.getElementById(mode + 'ScreenSizeGroup');
        const sqmGroup = document.getElementById(mode + 'SqmGroup');
        const sqmLabel = document.getElementById(mode + 'SqmLabel');
        const sqmInput = document.getElementById(mode === 'create' ? 'createSqmInput' : 'editSqm');
        const acuQtyGroup = document.getElementById(mode + 'AcuQtyGroup');
        const forecastedQtyGroup = document.getElementById(mode + 'ForecastedQtyGroup');
        const descLabel = document.getElementById(mode + 'DescriptionLabel');
        const descInput = document.getElementById(mode === 'create' ? 'createDescriptionInput' : 'editDescription');
        const subtitle = document.getElementById(mode + 'ModalSubtitle');

        const isLedService = (cat === 'LED Service Units' || cat === 'LED SERVICES UNITS');
        const isCentralLed = (cat === 'CENTRALIZED LED INVENTORY');
        const isPhilipsService = (cat === 'Philips Service Units' || cat === 'PHILIPS SERVICE UNITS');
        const isEolPhilips = (cat === 'EOL PHILIPS UNITS');
        const breakdownCard = document.getElementById(mode === 'create' ? 'createInventoryBreakdown' : 'editInventoryBreakdown');

        if (isLedService) {
            if (modalTitle && mode === 'create') modalTitle.textContent = 'Add LED Service Unit';
            if (tagLabel) tagLabel.textContent = 'CDX';
            if (tagInput) tagInput.placeholder = 'e.g. 1';
            if (tagHint) tagHint.classList.add('hidden');
            if (poInput) poInput.placeholder = 'e.g. PO-2026-0104';
            if (modelLabel) modelLabel.textContent = 'Model *';
            if (modelInput) modelInput.placeholder = 'e.g. P2.5 INDOOR';
            if (descLabel) descLabel.textContent = 'Item Description *';
            if (screenSizeGroup) screenSizeGroup.classList.add('hidden');
            if (acuQtyGroup) acuQtyGroup.classList.add('hidden');
            if (forecastedQtyGroup) forecastedQtyGroup.classList.add('hidden');
            if (sqmGroup) sqmGroup.classList.remove('hidden');
            if (sqmLabel) sqmLabel.textContent = 'Total Available SQM (m²)';
            if (sqmInput) sqmInput.placeholder = 'e.g. 12.00';
            if (breakdownCard) breakdownCard.classList.remove('hidden');
            if (descInput) descInput.placeholder = 'e.g. 500x500mm Die-Cast Aluminum Cabinet, High Refresh Rate, Front Serviceable Demo Unit';
            if (subtitle && mode === 'create') subtitle.textContent = 'Create a new entry for LED Service Units (Events & Demo)';
            if (mfg && (!mfg.value || mfg.value === 'PHILIPS')) {
                mfg.value = 'UNILUMIN';
            }
        } else if (isPhilipsService) {
            if (modalTitle && mode === 'create') modalTitle.textContent = 'Add Philips Service Unit';
            if (tagLabel) tagLabel.textContent = 'SERIAL NO.';
            if (tagInput) tagInput.placeholder = 'e.g. SN-PHILIPS-0012, SN-PHILIPS-0013';
            if (tagHint) tagHint.classList.remove('hidden');
            if (poInput) poInput.placeholder = 'e.g. PO-2026-0814';
            if (modelLabel) modelLabel.textContent = 'Model *';
            if (modelInput) modelInput.placeholder = 'e.g. 55BDL4050D';
            if (descLabel) descLabel.textContent = 'Particular / Item Description *';
            if (descInput) descInput.placeholder = 'e.g. 55" PHILIPS FLAT WIDE MONITOR';
            if (screenSizeGroup) screenSizeGroup.classList.remove('hidden');
            if (acuQtyGroup) acuQtyGroup.classList.remove('hidden');
            if (forecastedQtyGroup) forecastedQtyGroup.classList.remove('hidden');
            if (sqmGroup) sqmGroup.classList.add('hidden');
            if (breakdownCard) breakdownCard.classList.add('hidden');
            if (subtitle && mode === 'create') subtitle.textContent = 'Create a new entry for Philips Service Units (Events & Demo)';
            if (mfg) mfg.value = 'PHILIPS';
        } else if (isCentralLed) {
            if (modalTitle && mode === 'create') modalTitle.textContent = 'Add Centralized LED Unit';
            if (tagLabel) tagLabel.textContent = 'TAG #';
            if (tagInput) tagInput.placeholder = 'e.g. TAG-LED-009';
            if (tagHint) tagHint.classList.add('hidden');
            if (poInput) poInput.placeholder = 'e.g. PO-2026-0814';
            if (modelLabel) modelLabel.textContent = 'Model / Pixel Pitch *';
            if (modelInput) modelInput.placeholder = 'e.g. P2.5 Indoor';
            if (descLabel) descLabel.textContent = 'Item Description *';
            if (screenSizeGroup) screenSizeGroup.classList.add('hidden');
            if (acuQtyGroup) acuQtyGroup.classList.add('hidden');
            if (forecastedQtyGroup) forecastedQtyGroup.classList.add('hidden');
            if (sqmGroup) sqmGroup.classList.remove('hidden');
            if (sqmLabel) sqmLabel.textContent = 'SQM (Area m²)';
            if (sqmInput) sqmInput.placeholder = 'e.g. 12.50';
            if (breakdownCard) breakdownCard.classList.remove('hidden');
            if (descInput) descInput.placeholder = 'e.g. 500x500mm Die-Cast Aluminum Cabinet, High Refresh Rate';
            if (subtitle && mode === 'create') subtitle.textContent = 'Create a new entry in Centralized LED Inventory';
            if (mfg && (!mfg.value || mfg.value === 'PHILIPS')) {
                mfg.value = 'UNILUMIN';
            }
        } else if (isEolPhilips) {
            if (modalTitle && mode === 'create') modalTitle.textContent = 'Add Philips Display Unit';
            if (tagLabel) tagLabel.textContent = 'TAG / Serial #';
            if (tagInput) tagInput.placeholder = 'e.g. BDL-2026-01';
            if (tagHint) tagHint.classList.add('hidden');
            if (poInput) poInput.placeholder = 'e.g. PO-2024-001';
            if (modelLabel) modelLabel.textContent = 'Model *';
            if (modelInput) modelInput.placeholder = 'e.g. 55BDL4050D';
            if (descLabel) descLabel.textContent = 'Item Description *';
            if (screenSizeGroup) screenSizeGroup.classList.remove('hidden');
            if (acuQtyGroup) acuQtyGroup.classList.add('hidden');
            if (forecastedQtyGroup) forecastedQtyGroup.classList.add('hidden');
            if (sqmGroup) sqmGroup.classList.add('hidden');
            if (breakdownCard) breakdownCard.classList.add('hidden');
            if (descInput) descInput.placeholder = 'e.g. 55" PHILIPS FLAT WIDE MONITOR';
            if (subtitle && mode === 'create') subtitle.textContent = 'Create a new entry in Philips Display Inventory';
            if (mfg) mfg.value = 'PHILIPS';
        } else {
            if (modalTitle && mode === 'create') modalTitle.textContent = 'Add ' + cat;
            if (tagLabel) tagLabel.textContent = 'TAG / Serial #';
            if (tagInput) tagInput.placeholder = 'e.g. TAG-001';
            if (tagHint) tagHint.classList.add('hidden');
            if (poInput) poInput.placeholder = 'e.g. PO-2026-001';
            if (modelLabel) modelLabel.textContent = 'Model *';
            if (modelInput) modelInput.placeholder = 'e.g. Model / Hardware Name';
            if (descLabel) descLabel.textContent = 'Item Description *';
            if (screenSizeGroup) screenSizeGroup.classList.add('hidden');
            if (acuQtyGroup) acuQtyGroup.classList.add('hidden');
            if (forecastedQtyGroup) forecastedQtyGroup.classList.add('hidden');
            if (sqmGroup) sqmGroup.classList.add('hidden');
            if (breakdownCard) breakdownCard.classList.add('hidden');
            if (descInput) descInput.placeholder = 'e.g. Equipment and service unit description';
            if (subtitle && mode === 'create') subtitle.textContent = 'Create a new entry in ' + cat;
        }

        updateInventoryCalculation(mode);
    }

    function updateInventoryCalculation(mode) {
        const qtyEl = document.getElementById(mode === 'create' ? 'createQuantity' : 'editQuantity');
        const sqmEl = document.getElementById(mode === 'create' ? 'createSqmInput' : 'editSqm');
        const calcQty = document.getElementById(mode === 'create' ? 'createCalcQty' : 'editCalcQty');
        const calcAcuQty = document.getElementById(mode === 'create' ? 'createCalcAcuQty' : 'editCalcAcuQty');
        const calcPerPanel = document.getElementById(mode === 'create' ? 'createCalcPerPanel' : 'editCalcPerPanel');
        const calcTotalSqm = document.getElementById(mode === 'create' ? 'createCalcTotalSqm' : 'editCalcTotalSqm');

        const qty = parseFloat(qtyEl ? qtyEl.value : 0) || 0;
        const sqm = parseFloat(sqmEl ? sqmEl.value : 0) || 0;

        if (calcQty) calcQty.textContent = qty;
        if (calcAcuQty) calcAcuQty.textContent = qty;
        if (calcTotalSqm) calcTotalSqm.textContent = sqm > 0 ? sqm.toFixed(2) + ' m²' : '—';
        if (calcPerPanel) {
            if (qty > 0 && sqm > 0) {
                calcPerPanel.textContent = (sqm / qty).toFixed(3) + ' m²';
            } else {
                calcPerPanel.textContent = '—';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleCategoryFields('create');
        // Initial setup for default column view
        setColumnView('all');
    });

    // Edit Modal
    function openEditModal(item) {
        document.getElementById('editForm').action = '/admin/inventory/' + item.id;
        document.getElementById('editModalSubtitle').textContent = 'Model: ' + item.model + ' (' + item.manufacturer + ')';
        document.getElementById('editCategory').value = item.category || 'CENTRALIZED LED INVENTORY';
        document.getElementById('editTagNumber').value = item.tag_number || '';
        document.getElementById('editPoNumber').value = item.po_number || '';
        
        // Select Manufacturer in dropdown
        let mfgSelect = document.getElementById('editManufacturer');
        if (mfgSelect) {
            let found = false;
            const targetVal = (item.manufacturer || '').trim().toUpperCase();
            for (let i = 0; i < mfgSelect.options.length; i++) {
                if (mfgSelect.options[i].value.trim().toUpperCase() === targetVal) {
                    mfgSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found && item.manufacturer) {
                let newOpt = new Option(item.manufacturer.toUpperCase(), item.manufacturer.toUpperCase(), true, true);
                mfgSelect.add(newOpt);
            }
        }
        
        let checkInDate = '';
        if (item.check_in_date) {
            checkInDate = item.check_in_date.substring(0, 10);
        }
        document.getElementById('editCheckInDate').value = checkInDate;
        document.getElementById('editModel').value = item.model || '';
        document.getElementById('editScreenSize').value = item.screen_size || '';
        document.getElementById('editQuantity').value = item.quantity || 0;
        document.getElementById('editOriginalQty').value = item.original_quantity !== null && item.original_quantity !== undefined ? item.original_quantity : item.quantity;
        document.getElementById('editStatus').value = item.status || 'in_stock';
        document.getElementById('editRemarks').value = item.remarks || '';
        document.getElementById('editAcuQty').value = item.acu_quantity !== null && item.acu_quantity !== undefined ? item.acu_quantity : '';
        document.getElementById('editForecastedQty').value = item.forecasted_quantity || '';
        document.getElementById('editSqm').value = item.sqm || '';
        
        // Location dropdown selection
        let locSelect = document.getElementById('editLocation');
        if (locSelect) {
            let found = false;
            const targetLoc = (item.location || '').trim().toUpperCase();
            for (let i = 0; i < locSelect.options.length; i++) {
                if (locSelect.options[i].value.trim().toUpperCase() === targetLoc) {
                    locSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found && item.location) {
                let newOpt = new Option(item.location, item.location, true, true);
                locSelect.add(newOpt);
            }
        }

        document.getElementById('editDescription').value = item.item_description || '';

        toggleCategoryFields('edit');
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Delete Modal
    function openDeleteModal(item) {
        document.getElementById('deleteForm').action = '/admin/inventory/' + item.id;
        document.getElementById('deleteModalModel').textContent = item.model + ' (' + item.manufacturer + ')';
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Close on Escape Key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
            closeDrawer();
            closeModal('reserveModal');
        }
    });
</script>
@endsection
