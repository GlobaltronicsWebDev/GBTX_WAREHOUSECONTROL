<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | Globaltronics</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/globaltronics_logo.png') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
</head>
<body class="min-h-screen bg-white text-slate-950 antialiased">
    <main class="flex min-h-screen items-center justify-center px-6 py-12">
        <div class="w-full max-w-xs">
            <header class="mb-8 text-center">
                <svg class="mx-auto mb-10 h-7 w-7 text-amber-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm0 17a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm10-7a1 1 0 0 1-1 1h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1ZM5 12a1 1 0 0 1-1 1H3a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1Zm14.07-6.07a1 1 0 0 1 0 1.41l-.71.71a1 1 0 0 1-1.42-1.41l.71-.71a1 1 0 0 1 1.42 0ZM7.06 17.94a1 1 0 0 1 0 1.41l-.71.71a1 1 0 1 1-1.42-1.41l.71-.71a1 1 0 0 1 1.42 0Zm12.01 1.41a1 1 0 0 1-1.42.71l-.71-.71a1 1 0 0 1 1.42-1.41l.71.71a1 1 0 0 1 0 .7ZM7.06 6.06A1 1 0 0 1 5.64 7.47l-.71-.71a1 1 0 1 1 1.42-1.41l.71.71Z"/>
                    <circle cx="12" cy="12" r="5"/>
                </svg>
                <h1 class="text-4xl font-semibold leading-tight tracking-tight">Log in to your account</h1>
                <p class="mt-2 text-sm text-slate-500">Get your login from MIS</p>
            </header>

            @if (session('status'))
                <div role="status" class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-md border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700">
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
                        class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
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
                            class="w-full rounded-md border border-slate-300 px-3 py-2.5 pr-11 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        >
                        <button
                            type="button"
                            id="passwordToggle"
                            aria-label="Show password"
                            aria-pressed="false"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-800"
                        >
                            <svg id="passwordToggleIcon" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                <circle cx="12" cy="12" r="2.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >
                        Remember me
                    </label>
                    <button
                        type="button"
                        onclick="document.getElementById('forgotPasswordModal').showModal()"
                        class="text-sm text-slate-600 underline-offset-2 hover:text-blue-600 hover:underline"
                    >
                        Forgot password?
                    </button>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-blue-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Log in
                </button>
            </form>

            @auth
                <div class="mt-6 flex items-center justify-between rounded-md border border-slate-200 px-4 py-3 text-sm">
                    <span>Signed in as {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="font-medium text-blue-600 hover:text-blue-700">Log out</button>
                    </form>
                </div>
            @endauth
        </div>
    </main>

    <dialog id="forgotPasswordModal" class="w-full max-w-md rounded-xl border border-slate-200 p-0 shadow-xl backdrop:bg-slate-950/40">
        <div class="p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Reset your password</h2>
                    <p class="mt-1 text-sm text-slate-500">Enter your work email and we’ll send you a reset link.</p>
                </div>
                <button
                    type="button"
                    onclick="document.getElementById('forgotPasswordModal').close()"
                    aria-label="Close"
                    class="text-xl leading-none text-slate-500 hover:text-slate-800"
                >
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="recovery_email" class="mb-2 block text-sm font-medium">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="recovery_email"
                        autocomplete="email"
                        required
                        placeholder="email@example.com"
                        class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                </div>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        onclick="document.getElementById('forgotPasswordModal').close()"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Send reset link
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');

        passwordToggle.addEventListener('click', () => {
            const shouldShowPassword = passwordInput.type === 'password';

            passwordInput.type = shouldShowPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(shouldShowPassword));
            passwordToggle.setAttribute('aria-label', shouldShowPassword ? 'Hide password' : 'Show password');
        });
    </script>
</body>
</html>
