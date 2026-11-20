@extends('layouts.operations-dashboard')

@section('title', 'Profile Settings')
@section('breadcrumb', 'Profile')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex items-center justify-between border-b border-[#E5E7EB] pb-3">
            <h1 class="text-[16px] font-semibold text-slate-900">Profile Settings</h1>
            <div class="flex items-center gap-2 text-[10px] text-slate-500">
                <time id="dashboardDate">Loading date</time>
                <span class="h-4 w-px bg-[#E5E7EB]"></span>
                <time id="dashboardClock" class="font-mono tabular-nums text-slate-800">--:--:--</time>
            </div>
        </section>

        @if (session('status'))
            <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="flex flex-col gap-5 py-3 sm:flex-row sm:items-center">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-white bg-blue-600 text-2xl font-semibold text-white shadow-md">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-[18px] font-semibold text-slate-900">{{ $user->name }}</h2>
                <p class="mt-1 truncate text-[12px] text-slate-500">{{ $user->email }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] text-emerald-700">Active</span>
                    @if ($user->department)
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[10px] text-slate-700">{{ $user->department->name }}</span>
                    @endif
                    @if ($user->company)
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[10px] text-slate-700">{{ $user->company->name }}</span>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="#profileEditor" class="inline-flex h-8 items-center gap-1.5 border border-[#E5E7EB] px-3 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Profile</a>
                <a href="{{ route('admin.settings.security') }}" class="inline-flex h-8 items-center gap-1.5 border border-[#E5E7EB] px-3 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Account</a>
            </div>
        </section>

        @if ($user->email_verified_at)
            <div role="status" class="flex items-center gap-2 border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-700">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"/></svg>
                Your email address was verified.
            </div>
        @else
            <div role="status" class="border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] text-amber-800">Your email address is not verified.</div>
        @endif

        <section id="profileEditor" class="max-w-2xl border border-[#E5E7EB] bg-white p-4">
            <h3 class="text-[13px] font-semibold text-slate-900">Profile details</h3>
            <p class="mt-1 text-[11px] text-slate-500">Update your display name. Contact IT to change your login email.</p>
            <form method="POST" action="{{ route('admin.settings.profile.update') }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="mb-1 block text-[11px] font-medium text-slate-600">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required class="h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    @error('name') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-[11px] font-medium text-slate-600">Login email</label>
                    <input id="email" value="{{ $user->email }}" readonly class="h-9 w-full border border-[#E5E7EB] bg-[#F8F9FA] px-2 text-[12px] text-slate-500">
                </div>
                <div class="flex justify-end border-t border-[#E5E7EB] pt-3">
                    <button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Save profile</button>
                </div>
            </form>
        </section>
    </div>
@endsection
