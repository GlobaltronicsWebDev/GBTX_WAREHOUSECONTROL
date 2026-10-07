<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | Globaltronics Inc | ICS</title>
    
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/globaltronics_logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased selection:bg-blue-500 selection:text-white flex flex-col min-h-screen overflow-x-hidden">

    <div class="relative flex-1 min-h-screen flex flex-col lg:flex-row">
        
        <!-- LEFT HALF: Cinematic Warehouse Background & Industrial Hero -->
        <div class="relative w-full lg:w-1/2 min-h-[380px] lg:min-h-screen flex flex-col justify-between p-6 sm:p-10 lg:p-14 overflow-hidden border-b lg:border-b-0 lg:border-r border-slate-800/80 bg-slate-900">
            <!-- Background Image with Moody Gradient Overlays -->
            <div class="absolute inset-0 z-0">
                <img 
                    src="{{ asset('assets/images/logo/jrl_background.jpg') }}" 
                    alt="JRL Warehouse Logistics Hub" 
                    class="w-full h-full object-cover object-center scale-105 opacity-70"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-slate-900/40"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-transparent to-slate-950/90"></div>
                <div class="absolute inset-0 warehouse-pattern opacity-30 pointer-events-none"></div>
            </div>

            <!-- Top Left: Status Pill -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md border border-slate-700/60 text-xs font-medium text-slate-300 shadow-xl">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="font-mono-code text-[11px] text-emerald-400 font-semibold tracking-wide">SYSTEM ONLINE</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-300">WMS Security Gateway</span>
                </div>
            </div>

            <!-- Middle Left: Headline -->
            <div class="relative z-10 my-auto py-10 max-w-xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 mb-4 rounded-md bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold tracking-wider uppercase">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Credential Recovery Verification
                </div>
                
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
                    Authorized Access <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-400">
                        Password Reset.
                    </span>
                </h1>
                
                <p class="mt-4 text-sm sm:text-base text-slate-300 leading-relaxed max-w-lg">
                    Choose a strong, compliant password with at least 8 characters. Once updated, your credentials will sync across all warehouse terminal stations.
                </p>
            </div>

            <!-- Bottom Left -->
            <div class="relative z-10 pt-4 border-t border-slate-800/70 flex items-center justify-between text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                    <span>Globaltronics Security Protocol v4.1</span>
                </div>
                <div class="font-mono-code text-[11px] text-slate-400">256-BIT ENCRYPTED</div>
            </div>
        </div>

        <!-- RIGHT HALF: Password Reset Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 relative">
            
            <!-- Ambient Glow -->
            <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Security Portal</span>
                </div>
                
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1 py-1 px-2.5 rounded-md hover:bg-slate-800/60">
                    <span>← Back to Login</span>
                </a>
            </div>

            <div class="relative z-10 my-auto py-8 sm:py-12 w-full max-w-md mx-auto">
                
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-slate-900/90 border border-slate-700/80 shadow-2xl shadow-blue-950/60 mb-3.5 relative overflow-hidden">
                        <div class="absolute inset-0 bg-blue-500/15 rounded-3xl blur-xl pointer-events-none"></div>
                        <img 
                            src="{{ asset('assets/images/logo/globaltronics_logo.png') }}" 
                            alt="Globaltronics Logo" 
                            class="w-full h-full object-contain p-2 drop-shadow-2xl relative z-10"
                        />
                    </div>
                    
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        SET NEW PASSWORD
                    </h2>
                    <p class="text-xs uppercase tracking-[0.25em] text-blue-400 font-bold mt-1">
                        Globaltronics Security
                    </p>
                    <p class="text-xs text-slate-400 mt-2">
                        Enter your verified email and configure your new secure password
                    </p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl text-xs flex items-start gap-3 bg-red-500/15 border border-red-500/30 text-red-300">
                        <span class="mt-0.5">
                            <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div class="flex-1 font-medium">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Account Email <span class="text-blue-400">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                required 
                                value="{{ old('email', $email) }}"
                                placeholder="name@globaltronics.net" 
                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- New Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            New Password <span class="text-blue-400">*</span>
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
                                placeholder="Minimum 8 characters" 
                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Confirm New Password <span class="text-blue-400">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="password_confirmation" 
                                required 
                                placeholder="Re-type your password" 
                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button 
                            type="submit" 
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 hover:from-blue-600 hover:via-blue-500 hover:to-indigo-600 shadow-lg shadow-blue-900/30 active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2"
                        >
                            <span>UPDATE PASSWORD</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>

                    <div class="text-center pt-2">
                        <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-blue-400 transition-colors">
                            ← Return to Sign In
                        </a>
                    </div>
                </form>

            </div>

            <!-- Footer -->
            <div class="relative z-10 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    <span>All rights reserved 2026, Globaltronics Inc</span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
