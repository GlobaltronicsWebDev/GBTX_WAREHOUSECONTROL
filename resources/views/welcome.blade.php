<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-white dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | Globaltronics</title>

    <script>
        const savedTheme = localStorage.getItem('whms-theme');
        const prefersDarkTheme = window.matchMedia('(prefers-color-scheme: dark)').matches;

        document.documentElement.classList.toggle(
            'dark',
            savedTheme === 'dark' || (savedTheme === null && prefersDarkTheme)
        );
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/globaltronics_logo.png') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
    <style>
        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-white text-slate-950 antialiased dark:bg-slate-950 dark:text-slate-100">
    <main class="grid min-h-screen grid-cols-1 lg:grid-cols-2">
        <section aria-labelledby="welcome-title" class="relative isolate flex min-h-[320px] flex-col justify-between overflow-hidden bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 px-8 py-8 text-white sm:px-12 sm:py-10 lg:min-h-screen">
            <img
                src="{{ asset('assets/images/logo/GBTX_ROT.gif') }}"
                alt=""
                aria-hidden="true"
                class="pointer-events-none absolute -bottom-24 -right-24 w-96 opacity-10 sm:-bottom-32 sm:-right-32 sm:w-[36rem]"
            >

            <div class="relative z-10 flex items-center gap-3">
                <img
                    src="{{ asset('assets/images/logo/GBTX_ROT.gif') }}"
                    alt="Globaltronics logo"
                    class="h-auto w-20 object-contain"
                    width="80"
                    height="45"
                >
                <div>
                    <p class="text-sm font-bold tracking-[0.16em]">Inventory Control</p>
                    
                </div>
            </div>

            <div class="relative z-10 mt-12 max-w-lg lg:mt-0">
                <h2 id="welcome-title" class="text-4xl font-semibold tracking-tight sm:text-5xl">Good Day!</h2>
                <p class="mt-3 max-w-md text-sm leading-6 text-blue-100 sm:text-base">
                    Sign in to manage warehouse operations.
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-blue-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>
                    <time id="currentDate">{{ now()->format('l, F j, Y') }}</time>
                </div>
            </div>

            <div class="relative z-10 mt-12 flex flex-wrap items-center justify-between gap-3 text-xs text-blue-100/80">
                <p>Globaltronics Inc.</p>
                <time id="currentTime" class="font-mono text-sm tracking-wide text-white">{{ now()->format('h:i:s A') }}</time>
            </div>
        </section>

        <section class="relative flex min-h-[600px] items-center justify-center bg-white px-6 py-10 transition-colors sm:px-10 dark:bg-slate-950 lg:min-h-screen">
        <div class="w-full max-w-xs">
            <header class="mb-8 text-center">
                <div class="absolute right-6 top-6 z-20">
                    <button
                        type="button"
                        id="themeToggle"
                        aria-label="Switch to dark mode"
                        aria-pressed="false"
                        title="Switch to dark mode"
                        class="relative inline-flex h-10 w-20 items-center rounded-full bg-slate-100 p-1 shadow-inner ring-1 ring-slate-200 transition-colors hover:bg-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:bg-slate-800 dark:ring-slate-700 dark:hover:bg-slate-700"
                    >
                        <svg class="absolute right-2 h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm0 17a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm10-7a1 1 0 0 1-1 1h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1ZM5 12a1 1 0 0 1-1 1H3a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1Zm14.07-6.07a1 1 0 0 1 0 1.41l-.71.71a1 1 0 0 1-1.42-1.41l.71-.71a1 1 0 0 1 1.42 0ZM7.06 17.94a1 1 0 0 1 0 1.41l-.71.71a1 1 0 1 1-1.42-1.41l.71-.71a1 1 0 0 1 1.42 0Zm12.01 1.41a1 1 0 0 1-1.42.71l-.71-.71a1 1 0 0 1 1.42-1.41l.71.71a1 1 0 0 1 0 .7ZM7.06 6.06A1 1 0 0 1 5.64 7.47l-.71-.71a1 1 0 1 1 1.42-1.41l.71.71Z"/>
                            <circle cx="12" cy="12" r="5"/>
                        </svg>
                        <svg class="absolute left-2 h-4 w-4 text-slate-400 dark:text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/>
                        </svg>
                        <span class="relative z-10 h-8 w-8 rounded-full bg-white shadow-md transition-transform duration-300 dark:translate-x-10 dark:bg-slate-100"></span>
                    </button>
                </div>
                <h1 class="text-4xl font-semibold leading-tight tracking-tight text-slate-950 dark:text-white sm:text-5xl">Log in to your account</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Login in Warehouse Management System</p>
            </header>

            @if (session('status'))
                <div role="status" class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-md border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login', [], false) }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium">Email address</label>
                    <input
                        type="email"
                        name="username"
                        id="email"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required
                        autofocus
                        placeholder="email@example.com"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-400 dark:focus:ring-blue-400/20"
                    >
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            autocomplete="current-password"
                            required
                            placeholder="Password"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-400 dark:focus:ring-blue-400/20"
                        >
                        <button
                            type="button"
                            id="passwordToggle"
                            aria-label="Show password"
                            aria-pressed="false"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            <svg id="passwordToggleIcon" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                <circle cx="12" cy="12" r="2.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900"
                        >
                        Remember me
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-blue-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Log in
                </button>
            </form>

            @auth
                <div class="mt-6 flex items-center justify-between rounded-md border border-slate-200 px-4 py-3 text-sm dark:border-slate-700">
                    <span>Signed in as {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="font-medium text-blue-600 hover:text-blue-700">Log out</button>
                    </form>
                </div>
            @endauth
        </div>
            </section>
    </main>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const applyTheme = (isDark) => {
            document.documentElement.classList.toggle('dark', isDark);
            localStorage.setItem('whms-theme', isDark ? 'dark' : 'light');
            themeToggle.setAttribute('aria-pressed', String(isDark));
            themeToggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            themeToggle.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        };

        const isDarkMode = document.documentElement.classList.contains('dark');
        themeToggle.setAttribute('aria-pressed', String(isDarkMode));
        themeToggle.setAttribute('aria-label', isDarkMode ? 'Switch to light mode' : 'Switch to dark mode');
        themeToggle.setAttribute('title', isDarkMode ? 'Switch to light mode' : 'Switch to dark mode');
        themeToggle.addEventListener('click', () => {
            applyTheme(!document.documentElement.classList.contains('dark'));
        });

        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');

        passwordToggle.addEventListener('click', () => {
            const shouldShowPassword = passwordInput.type === 'password';

            passwordInput.type = shouldShowPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(shouldShowPassword));
            passwordToggle.setAttribute('aria-label', shouldShowPassword ? 'Hide password' : 'Show password');
        });

        const welcomeTitle = document.getElementById('welcome-title');
        const currentDate = document.getElementById('currentDate');
        const currentTime = document.getElementById('currentTime');
        const dateFormatter = new Intl.DateTimeFormat('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
        const timeFormatter = new Intl.DateTimeFormat('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });

        const updateWelcomeClock = () => {
            const now = new Date();

            welcomeTitle.textContent = now.getHours() < 12 ? 'Good Day!' : 'Good afternoon!';
            currentDate.textContent = dateFormatter.format(now);
            currentTime.textContent = timeFormatter.format(now);
        };

        updateWelcomeClock();
        window.setInterval(updateWelcomeClock, 1000);
    </script>
</body>
</html>
