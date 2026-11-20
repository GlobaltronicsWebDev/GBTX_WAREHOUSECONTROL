<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-white dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirm password | Globaltronics</title>
    <script>
        const savedTheme = localStorage.getItem('whms-theme');
        const prefersDarkTheme = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', savedTheme === 'dark' || (savedTheme === null && prefersDarkTheme));
    </script>
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
            font-family: Poppins, ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-white text-slate-950 antialiased dark:bg-slate-950 dark:text-slate-100">
    <main class="grid min-h-screen grid-cols-1 lg:grid-cols-2">
        <section class="flex min-h-[280px] flex-col justify-between bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 px-8 py-8 text-white sm:px-12 sm:py-10 lg:min-h-screen">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/images/logo/GBTX_ROT.gif') }}" alt="Globaltronics logo" class="h-auto w-14 object-contain" width="56" height="32">
                <p class="text-sm font-semibold tracking-wide">Inventory</p>
            </div>
            <div class="mt-10">
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">Welcome!</h1>
                <p class="mt-4 flex items-center gap-2 text-sm text-blue-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>
                    <time id="confirmDate">{{ now()->format('l, F j, Y') }}</time>
                    <span>·</span>
                    <time id="confirmTime">{{ now()->format('h:i:s A') }}</time>
                </p>
            </div>
            <p class="mt-10 text-xs text-blue-100">Globaltronics Inc.</p>
        </section>

        <section class="relative flex min-h-[600px] items-center justify-center px-6 py-12 transition-colors dark:bg-slate-950 sm:px-10 lg:min-h-screen">
            <button type="button" id="themeToggle" class="absolute right-6 top-6 rounded-full p-2 text-amber-500 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-blue-500 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
                </svg>
            </button>
            <div class="w-full max-w-sm">
                <div class="mb-7 text-center">
                    <div class="mb-4 flex justify-center text-blue-500" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="4"/><path d="M12 1v3m0 16v3m11-11h-3M4 12H1m18.78-7.78-2.12 2.12M6.34 17.66l-2.12 2.12m15.56 0-2.12-2.12M6.34 6.34 4.22 4.22"/></svg>
                    </div>
                    <h2 class="text-3xl font-semibold leading-tight tracking-tight">Confirm password</h2>
                    <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">This is a secure area. Please confirm your password before continuing.</p>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-4 border border-red-200 bg-red-50 px-3 py-2.5 text-xs text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ $errors->first() }}</div>
                @endif

                <div class="mb-5 flex items-center gap-3 text-[10px] uppercase text-slate-400" aria-hidden="true">
                    <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                    <span>Or confirm with password</span>
                    <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                </div>

                <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="password" class="mb-1.5 block text-xs font-medium">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" autocomplete="current-password" required autofocus class="h-10 w-full border border-slate-300 bg-white px-3 pr-10 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900">
                            <button type="button" id="passwordToggle" class="absolute inset-y-0 right-0 px-3 text-slate-400" aria-label="Show password">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="h-10 w-full bg-blue-600 text-xs font-medium text-white hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">Confirm password</button>
                </form>
            </div>
        </section>
    </main>
    <script>
        const passwordInput = document.getElementById('password');
        document.getElementById('passwordToggle').addEventListener('click', (event) => {
            const visible = passwordInput.type === 'text';
            passwordInput.type = visible ? 'password' : 'text';
            event.currentTarget.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });

        document.getElementById('themeToggle').addEventListener('click', () => {
            const dark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('whms-theme', dark ? 'dark' : 'light');
        });

        const updateClock = () => {
            const now = new Date();
            document.getElementById('confirmDate').textContent = new Intl.DateTimeFormat('en-US', {
                weekday: 'long', month: 'long', day: 'numeric', year: 'numeric',
            }).format(now);
            document.getElementById('confirmTime').textContent = new Intl.DateTimeFormat('en-US', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true,
            }).format(now);
        };
        updateClock();
        window.setInterval(updateClock, 1000);
    </script>
</body>
</html>
