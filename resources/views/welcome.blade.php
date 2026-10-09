<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Globaltronics Inc | ICS</title>
    
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/globaltronics_logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

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
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .glass-panel-light {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        /* Custom scrollbar for modals */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="min-h-full bg-white text-slate-800 antialiased selection:bg-amber-500 selection:text-white flex flex-col">

    <div class="relative flex-1 min-h-screen flex flex-col lg:flex-row bg-white">
        
        <!-- LEFT HALF: Cinematic Warehouse Background & Industrial Hero -->
        <div class="relative w-full lg:w-1/2 shrink-0 lg:min-h-screen flex flex-col justify-between p-6 sm:p-10 lg:p-14 overflow-hidden border-b lg:border-b-0 lg:border-r border-slate-800/80 bg-slate-900">
            <!-- Background Image with Moody Gradient Overlays -->
            <div class="absolute inset-0 z-0">
                <img 
                    src="{{ asset('assets/images/logo/jrl_background.jpg') }}" 
                    alt="JRL Warehouse Logistics Hub" 
                    class="w-full h-full object-cover object-center scale-105 transform motion-safe:transition-transform motion-safe:duration-10000 motion-safe:hover:scale-110 opacity-70"
                />
                <!-- Multi-layer gradient overlays for readability and luxury tone -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-slate-900/40"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-transparent to-slate-950/90"></div>
                <div class="absolute inset-0 warehouse-pattern opacity-30 pointer-events-none"></div>
            </div>

            <!-- Top Left: Live Status Pill & System Identity -->
            <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md border border-slate-700/60 text-xs font-medium text-slate-300 shadow-xl">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="font-mono-code text-[11px] text-emerald-400 font-semibold tracking-wide">SYSTEM ONLINE</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-300">WMS v2.6.4</span>
                </div>

                <div class="flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-900/60 backdrop-blur-md border border-slate-800 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Facility: Globaltronics Inc. Warehouse</span>
                </div>
            </div>

            <!-- Middle Left: Warehouse Headline, Description & Metrics -->
            <div class="relative z-10 flex-1 flex flex-col justify-center my-6 lg:my-auto py-6 sm:py-8 lg:py-10 max-w-xl">
                <h1 class="text-3xl sm:text-4xl lg:text-4xl xl:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
                    GLOBALTRONICS WAREHOUSE <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-amber-400">
                        INVENTORY CONTROL SYSTEM
                    </span>
                </h1>

                <p class="mt-4 text-sm sm:text-base text-slate-300 leading-relaxed max-w-lg">
                    Real-time stock dispatch, barcode tracking, pallet management, and high-velocity fulfillment powered by Globaltronics automation infrastructure.
                </p>

                <!-- Warehouse Live Metrics Cards -->
                <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-lg">
                    <div class="p-3.5 rounded-xl bg-slate-900/70 backdrop-blur-md border border-slate-800/80 shadow-lg">
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">SKUs Synced</div>
                        <div class="mt-1 text-lg sm:text-xl font-bold text-white font-mono-code">14,280+</div>
                        <div class="text-[10px] text-emerald-400 mt-0.5 flex items-center gap-1 font-medium">
                            <span>↑ 99.98%</span> uptime
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/70 backdrop-blur-md border border-slate-800/80 shadow-lg">
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Bay Utilization</div>
                        <div class="mt-1 text-lg sm:text-xl font-bold text-amber-400 font-mono-code">92.4%</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-medium">Zone A to D</div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/70 backdrop-blur-md border border-slate-800/80 shadow-lg">
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Security</div>
                        <div class="mt-1 text-lg sm:text-xl font-bold text-blue-400 font-mono-code">256-Bit</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-medium">AES Encrypted</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left: Badge and Notice -->
            <div class="relative z-10 pt-4 border-t border-slate-800/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                    <span>Globaltronics Automated Facility Network</span>
                </div>
                <div class="text-[11px] font-mono-code text-slate-400">
                    ID: WMS-PH-CEB-01
                </div>
            </div>
        </div>

        <!-- RIGHT HALF: Modern Interactive Login Experience -->
        <div class="w-full lg:w-1/2 shrink-0 lg:min-h-screen flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-white text-slate-800 relative border-l border-slate-200">
            
            <!-- Ambient Glow Accents (Subtle Light Tones) -->
            <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-blue-100/50 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/3 w-80 h-80 bg-amber-100/40 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Header & Language / Support Help -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Warehouse Terminal Gateway</span>
                </div>
                
                <a href="#helpModal" onclick="document.getElementById('helpModal').classList.remove('hidden')" class="text-xs text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1.5 py-1 px-2.5 rounded-lg border border-slate-200 hover:bg-slate-50">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Support</span>
                </a>
            </div>

            <!-- Center Form Container -->
            <div class="relative z-10 my-auto py-8 sm:py-12 w-full max-w-md mx-auto">
                
                <!-- Dynamic Mode Banner (Shows when Admin Portal is active) -->
                <div id="adminPortalBanner" class="hidden mb-6 p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-center justify-between animate-fadeIn">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1 rounded-md bg-amber-100 text-amber-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        <div>
                            <span class="font-bold tracking-wide">IT & WAREHOUSE ADMIN PORTAL</span>
                            <p class="text-[11px] text-amber-700">Elevated security clearance required</p>
                        </div>
                    </div>
                    <button type="button" onclick="toggleAdminMode(false)" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline underline-offset-2 ml-2">
                        Exit Admin
                    </button>
                </div>

                <!-- LOGO SECTION -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white border border-slate-200 shadow-xl shadow-slate-200/80 mb-3.5 group hover:border-blue-400 transition-all duration-300 relative overflow-hidden">
                        <img 
                            src="{{ asset('assets/images/logo/globaltronics_logo.png') }}" 
                            alt="Globaltronics Logo" 
                            class="w-full h-full object-contain p-2.5 drop-shadow group-hover:scale-105 transition-transform duration-300 relative z-10"
                        />
                    </div>
                    
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 flex items-center justify-center gap-2">
                        <span>GLOBALTRONICS</span>
                    </h2>
                    <p class="text-xs uppercase tracking-[0.28em] text-blue-600 font-bold mt-1">
                        Warehouse Management System
                    </p>
                    <p id="portalSubheading" class="text-xs text-slate-500 mt-2">
                        Enter your credentials to access warehouse operations
                    </p>
                </div>

                <!-- Status and Session Alerts -->
                @if (session('status'))
                    <div class="mb-5 p-3.5 rounded-xl text-xs flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800">
                        <span class="mt-0.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div class="flex-1 font-medium">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                @auth
                    <div class="mb-6 p-4 rounded-2xl bg-slate-50 border border-emerald-500/40 text-xs shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <div>
                                    <p class="text-[11px] text-slate-500">Authenticated Session</p>
                                    <p class="text-sm font-bold text-slate-900">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] font-mono-code text-slate-600">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition-colors shadow">
                                    Admin Dashboard →
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-xs transition-colors shadow">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                <!-- Error / Feedback Alert Box (interactive feedback) -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl text-xs flex items-start gap-3 bg-red-50 border border-red-200 text-red-700">
                        <span class="mt-0.5">
                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div class="flex-1 font-medium">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <div id="statusAlert" class="hidden mb-5 p-3.5 rounded-xl text-xs flex items-start gap-3">
                    <span id="alertIcon" class="mt-0.5"></span>
                    <div id="alertMessage" class="flex-1 font-medium"></div>
                </div>

                <!-- LOGIN FORM -->
                <form id="loginForm" method="POST" action="{{ route('login', [], false) }}" onsubmit="handleFormSubmit(event)" autocomplete="off" class="space-y-4">
                    @csrf

                    <!-- Hidden traps to intercept aggressive browser autofill -->
                    <input type="text" style="display:none" autocomplete="off" />
                    <input type="password" style="display:none" autocomplete="off" />

                    <!-- Hidden input to track role selection -->
                    <input type="hidden" name="login_type" id="loginTypeInput" value="standard">

                    <!-- USERNAME / EMAIL Input Field -->
                    <div>
                        <label for="login_identity" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            <span id="identityLabel">Username or Email</span>
                            <span class="text-amber-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="username" 
                                id="login_identity" 
                                required 
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                                spellcheck="false"
                                value=""
                                placeholder="e.g. staff@globaltronics.net" 
                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- PASSWORD Input Field -->
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Password
                            <span class="text-amber-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required 
                                autocomplete="new-password"
                                value=""
                                placeholder="••••••••••••" 
                                class="w-full pl-10 pr-11 py-3 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            />
                            <!-- Toggle Password Visibility Button -->
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()" 
                                aria-label="Toggle password visibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                            >
                                <svg id="eyeIcon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeOffIcon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember_me" 
                                class="w-4 h-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500 focus:ring-offset-white transition-colors cursor-pointer"
                            />
                            <span class="text-xs text-slate-600">Keep me logged in for 30 days</span>
                        </label>
                    </div>

                    <!-- LOGIN SUBMIT BUTTON -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            id="submitButton"
                            class="w-full relative group overflow-hidden py-3 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 hover:from-blue-600 hover:via-blue-500 hover:to-indigo-600 shadow-lg shadow-blue-500/25 active:scale-[0.99] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-white flex items-center justify-center gap-2"
                        >
                            <span id="buttonSpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="buttonText">LOGIN</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Wireframe Link: Forgot Password directly below LOGIN button -->
                    <div class="text-center pt-3">
                        <a 
                            href="#forgotPasswordModal" 
                            onclick="openModal('forgotPasswordModal')" 
                            class="text-xs text-slate-500 hover:text-blue-600 font-medium transition-colors"
                        >
                            Forgot Password?
                        </a>
                    </div>
                </form>

                <!-- DIVIDER -->
                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-medium tracking-wider">
                            Or Switch Access Level
                        </span>
                    </div>
                </div>

                <!-- LOGIN AS IT/WAREHOUSE ADMIN BUTTON (Exact wireframe element with arrow) -->
                <div>
                    <button 
                        type="button" 
                        id="adminToggleBtn"
                        onclick="toggleAdminMode()"
                        class="w-full py-3.5 px-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-amber-500/60 text-slate-700 hover:text-amber-600 font-bold text-xs tracking-wider uppercase transition-all duration-200 flex items-center justify-center gap-2 group shadow-sm"
                    >
                        <span class="p-1 rounded bg-amber-500/10 text-amber-500 group-hover:bg-amber-500/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        <span id="adminToggleText">LOGIN AS IT/WAREHOUSE ADMIN</span>
                        <span class="text-amber-500 group-hover:translate-x-1.5 transition-transform duration-200 font-bold text-base leading-none">➔</span>
                    </button>
                </div>

            </div>

            <!-- FOOTER (Exact wireframe element: "All rights reserved 2026, Globaltronics Inc") -->
            <div class="relative z-10 pt-6 border-t border-slate-200 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    <span>All rights reserved 2026, Globaltronics Inc</span>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL 1: FORGOT PASSWORD -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-2xl p-6 sm:p-8 animate-fadeIn">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-amber-500/10 text-amber-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Reset Warehouse Password</h3>
                        <p class="text-xs text-slate-500">Security-verified account recovery</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('forgotPasswordModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
                @csrf
                <p class="text-xs text-slate-600 leading-relaxed">
                    Enter your registered employee email address. An authorization password reset link will be dispatched to your inbox.
                </p>
                <div>
                    <label for="recovery_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Employee Work Email</label>
                    <input 
                        type="email" 
                        name="email"
                        id="recovery_email" 
                        required 
                        placeholder="e.g. mark.estoesta@globaltronics.net" 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('forgotPasswordModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20">
                        Send Recovery Link
                    </button>
                </div>
            </form>
        </div>
    </div>



    <!-- MODAL 3: SYSTEM INFO & HELP -->
    <div id="helpModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-2xl p-6 sm:p-8 animate-fadeIn">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-amber-50 text-amber-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Globaltronics IT Support</h3>
                        <p class="text-xs text-slate-500">Warehouse Infrastructure Support</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('helpModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs text-slate-600">
                <p>For urgent access lockout, terminal kiosk resets, or role elevations:</p>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5 font-mono-code text-[11px]">
                    <div class="text-slate-600">Facility Hotline: <span class="text-amber-600 font-bold">+1 (800) 555-JRL-WMS</span></div>
                    <div class="text-slate-600">Internal Ext: <span class="text-slate-900 font-bold">4091 / 4092</span></div>
                    <div class="text-slate-600">Emergency NOC: <span class="text-emerald-600 font-bold">noc@globaltronics.com</span></div>
                </div>
                <p class="text-[11px] text-slate-400 pt-2">
                    Authorized personnel only. All access attempts and session keys are logged in accordance with Globaltronics 2026 security policies.
                </p>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" onclick="closeModal('helpModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Client-side Interactive JavaScript -->
    <script>
        // Track whether in Admin mode or Staff mode
        let isAdminMode = false;

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }

        function toggleAdminMode(forceState = null) {
            isAdminMode = forceState !== null ? forceState : !isAdminMode;
            
            const banner = document.getElementById('adminPortalBanner');
            const identityLabel = document.getElementById('identityLabel');
            const identityInput = document.getElementById('login_identity');
            const buttonText = document.getElementById('buttonText');
            const submitBtn = document.getElementById('submitButton');
            const adminToggleText = document.getElementById('adminToggleText');
            const loginTypeInput = document.getElementById('loginTypeInput');
            const subheading = document.getElementById('portalSubheading');
            const registerContainer = document.getElementById('registerContainer');

            if (isAdminMode) {
                banner.classList.remove('hidden');
                if (registerContainer) registerContainer.classList.add('hidden'); // Hide registration in IT / Warehouse Admin mode
                identityLabel.textContent = 'Admin Email';
                identityInput.placeholder = 'e.g. mark.estoesta@globaltronics.net';
                buttonText.textContent = 'LOGIN TO ADMIN PORTAL';
                submitBtn.classList.remove('from-blue-700', 'via-blue-600', 'to-indigo-700');
                submitBtn.classList.add('from-amber-600', 'via-amber-500', 'to-yellow-600', 'hover:from-amber-500', 'hover:to-yellow-500', 'shadow-amber-900/40');
                adminToggleText.textContent = 'SWITCH TO WAREHOUSE STAFF LOGIN';
                loginTypeInput.value = 'admin';
                subheading.textContent = 'Elevated IT & Warehouse Administrative Clearance';
            } else {
                banner.classList.add('hidden');
                identityLabel.textContent = 'Username or Email';
                identityInput.placeholder = 'e.g. staff@globaltronics.net';
                buttonText.textContent = 'LOGIN';
                submitBtn.classList.add('from-blue-700', 'via-blue-600', 'to-indigo-700');
                submitBtn.classList.remove('from-amber-600', 'via-amber-500', 'to-yellow-600', 'hover:from-amber-500', 'hover:to-yellow-500', 'shadow-amber-900/40');
                adminToggleText.textContent = 'LOGIN AS IT/WAREHOUSE ADMIN';
                loginTypeInput.value = 'standard';
                subheading.textContent = 'Enter your credentials to access warehouse operations';
            }
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Close modal on Escape key
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal('forgotPasswordModal');
                closeModal('helpModal');
            }
        });

        // Close modal on backdrop click
        ['forgotPasswordModal', 'helpModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('click', (e) => {
                    if (e.target === el) {
                        closeModal(id);
                    }
                });
            }
        });

        function showAlert(type, message) {
            const alertBox = document.getElementById('statusAlert');
            const alertMessage = document.getElementById('alertMessage');
            const alertIcon = document.getElementById('alertIcon');

            alertBox.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-700', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'border');

            if (type === 'error') {
                alertBox.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
                alertIcon.innerHTML = `<svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
            } else {
                alertBox.classList.add('bg-emerald-50', 'border', 'border-emerald-200', 'text-emerald-800');
                alertIcon.innerHTML = `<svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
            }

            alertMessage.textContent = message;
        }

        function handleFormSubmit(event) {
            const username = document.getElementById('login_identity').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submitButton');
            const spinner = document.getElementById('buttonSpinner');

            if (!username || !password) {
                event.preventDefault();
                showAlert('error', 'Please enter both your email/username and password.');
                return;
            }

            // Show interactive loading state and let form submit natively to POST /login
            if (spinner) spinner.classList.remove('hidden');
            if (submitBtn) {
                submitBtn.classList.add('opacity-80', 'cursor-wait');
            }
        }

        // Force clear any browser-injected credentials on fresh load
        window.addEventListener('load', () => {
            setTimeout(() => {
                const idInput = document.getElementById('login_identity');
                const pwdInput = document.getElementById('password');
                if (idInput && !idInput.matches(':focus')) idInput.value = '';
                if (pwdInput && !pwdInput.matches(':focus')) pwdInput.value = '';
            }, 60);
        });
    </script>
</body>
</html>
