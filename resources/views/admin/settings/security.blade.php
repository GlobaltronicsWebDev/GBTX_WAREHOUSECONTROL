@extends('layouts.operations-dashboard')

@section('title', 'Security Settings')
@section('breadcrumb', 'Security')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex items-center justify-between border-b border-[#E5E7EB] pb-3">
            <div>
                <h1 class="text-[16px] font-semibold text-slate-900">Security</h1>
                <p class="mt-1 text-[11px] text-slate-500">Manage your account password and sign-in security.</p>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-slate-500">
                <time id="dashboardDate">Loading date</time>
                <span class="h-4 w-px bg-[#E5E7EB]"></span>
                <time id="dashboardClock" class="font-mono tabular-nums text-slate-800">--:--:--</time>
            </div>
        </section>

        @if (session('status'))
            <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="max-w-2xl border border-[#E5E7EB] bg-white p-4">
            <div class="border-b border-[#E5E7EB] pb-3">
                <h2 class="text-[13px] font-semibold text-slate-900">Change password</h2>
                <p class="mt-1 text-[11px] text-slate-500">You confirmed your password to open this page. Choose a new password with at least 8 characters.</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.security.password') }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label for="password" class="mb-1 block text-[11px] font-medium text-slate-600">New password</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required class="h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    @error('password') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-[11px] font-medium text-slate-600">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required class="h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                </div>
                <div class="flex justify-end border-t border-[#E5E7EB] pt-3">
                    <button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Update password</button>
                </div>
            </form>
        </section>
    </div>
@endsection
