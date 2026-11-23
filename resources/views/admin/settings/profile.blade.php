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
        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[11px] text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="flex flex-wrap items-center justify-between gap-3 border border-[#E5E7EB] bg-white px-4 py-3" aria-labelledby="appearanceHeading">
            <div>
                <h2 id="appearanceHeading" class="text-[12px] font-semibold text-slate-900">Appearance</h2>
                <p class="mt-1 text-[11px] text-slate-500">Choose the theme for this browser.</p>
            </div>
            <button type="button" data-theme-toggle aria-label="Dark mode" aria-pressed="false" class="inline-flex items-center gap-2 rounded-sm border border-[#E5E7EB] px-3 py-2 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">
                Dark mode <span data-theme-state class="font-medium">Off</span>
            </button>
        </section>

        <section class="flex flex-col gap-5 py-3 sm:flex-row sm:items-center">
            @if ($user->profile_photo_path)
                <img id="profileSummaryPhoto" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}" alt="Profile photo" class="h-20 w-20 shrink-0 rounded-full border-4 border-white object-cover shadow-md">
                <div id="profileSummaryInitials" class="hidden h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-white bg-blue-600 text-2xl font-semibold text-white shadow-md">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @else
                <img id="profileSummaryPhoto" alt="Profile photo" class="hidden h-20 w-20 shrink-0 rounded-full border-4 border-white object-cover shadow-md">
                <div id="profileSummaryInitials" class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-white bg-blue-600 text-2xl font-semibold text-white shadow-md">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
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
                <button type="button" data-open-modal="editProfileModal" class="inline-flex h-8 items-center gap-1.5 border border-[#E5E7EB] px-3 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Profile</button>
                <button type="button" data-open-modal="editAccountModal" class="inline-flex h-8 items-center gap-1.5 border border-[#E5E7EB] px-3 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Account</button>
            </div>
        </section>

        @if ($user->email_verified_at)
            <div role="status" class="flex items-center gap-2 border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-700">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"/></svg>
                Your email address was verified.
            </div>
        @else
            <div role="status" class="flex flex-col gap-3 border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] text-amber-800 sm:flex-row sm:items-center sm:justify-between">
                <span>Your email address is not verified. Check your inbox or request a new verification email.</span>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="h-8 border border-amber-300 bg-white px-3 text-[11px] font-medium text-amber-900 hover:bg-amber-100">Resend verification email</button>
                </form>
            </div>
        @endif
    </div>

    <div id="editProfileModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-0 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="editProfileTitle">
        <div class="flex max-h-screen w-full max-w-2xl flex-col bg-white shadow-xl sm:max-h-[94vh]">
            <header class="flex items-start justify-between border-b border-[#E5E7EB] px-6 py-3">
                <div>
                    <h2 id="editProfileTitle" class="text-[15px] font-semibold text-slate-900">Edit Profile</h2>
                    <p class="mt-2 text-[12px] text-slate-500">Update your profile information.</p>
                </div>
                <button type="button" data-close-modal aria-label="Close edit profile" class="text-xl leading-none text-slate-500 hover:text-slate-900">×</button>
            </header>
            <form method="POST" action="{{ route('admin.settings.profile.update') }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_modal" value="profile">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <div>
                        <label for="first_name" class="mb-1 block text-[11px] font-medium text-slate-800">First Name</label>
                        <input id="first_name" name="first_name" value="{{ old('settings_modal') === 'profile' ? old('first_name') : $firstName }}" required maxlength="120" class="h-9 w-full rounded-md border border-slate-200 px-3 text-[12px] shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        @error('first_name') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="mb-1 block text-[11px] font-medium text-slate-800">Last Name</label>
                        <input id="last_name" name="last_name" value="{{ old('settings_modal') === 'profile' ? old('last_name') : $lastName }}" maxlength="120" class="h-9 w-full rounded-md border border-slate-200 px-3 text-[12px] shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        @error('last_name') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="company_id" class="mb-1 block text-[11px] font-medium text-slate-800">Company</label>
                        <select id="company_id" name="company_id" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-[12px] shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            <option value="">No company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('settings_modal') === 'profile' ? old('company_id') == $company->id : $user->company_id == $company->id)>{{ $company->name }}{{ $company->is_active ? '' : ' (Inactive)' }}</option>
                            @endforeach
                        </select>
                        @error('company_id') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="department_id" class="mb-1 block text-[11px] font-medium text-slate-800">Department</label>
                        <select id="department_id" name="department_id" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-[12px] shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            <option value="">No department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('settings_modal') === 'profile' ? old('department_id') == $department->id : $user->department_id == $department->id)>{{ $department->name }}{{ $department->is_active ? '' : ' (Inactive)' }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <footer class="space-y-2 border-t border-[#E5E7EB] px-6 py-4">
                    <button type="submit" class="h-10 w-full rounded-md bg-blue-600 text-[12px] font-medium text-white hover:bg-blue-700">Save Changes</button>
                    <button type="button" data-close-modal class="h-10 w-full rounded-md border border-slate-200 text-[12px] text-slate-800 hover:bg-slate-50">Cancel</button>
                </footer>
            </form>
        </div>
    </div>

    <div id="editAccountModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-0 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="editAccountTitle">
        <div class="flex max-h-screen w-full max-w-2xl flex-col bg-white shadow-xl sm:max-h-[94vh]">
            <header class="flex items-start justify-between border-b border-[#E5E7EB] px-6 py-3">
                <div>
                    <h2 id="editAccountTitle" class="text-[15px] font-semibold text-slate-900">Edit Account</h2>
                    <p class="mt-2 text-[12px] text-slate-500">Update your account information.</p>
                </div>
                <button type="button" data-close-modal aria-label="Close edit account" class="text-xl leading-none text-slate-500 hover:text-slate-900">×</button>
            </header>
            <form method="POST" action="{{ route('admin.settings.profile.account') }}" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_modal" value="account">
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-5 sm:space-y-0">
                    <div class="grid gap-5 sm:grid-cols-[170px_1fr]">
                        <div class="flex flex-col items-center gap-3">
                            @if ($user->profile_photo_path)
                                <img id="profilePhotoPreview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}" alt="Profile photo preview" class="h-32 w-32 rounded-full border-4 border-white object-cover shadow-md">
                                <span id="profilePhotoInitials" class="hidden h-32 w-32 items-center justify-center rounded-full border-4 border-white bg-blue-600 text-3xl font-semibold text-white shadow-md">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @else
                                <img id="profilePhotoPreview" src="" alt="Profile photo preview" class="hidden h-32 w-32 rounded-full border-4 border-white object-cover shadow-md">
                                <span id="profilePhotoInitials" class="flex h-32 w-32 items-center justify-center rounded-full border-4 border-white bg-blue-600 text-3xl font-semibold text-white shadow-md">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                            <label for="profile_photo" class="cursor-pointer rounded-md border border-slate-200 px-3 py-2 text-[12px] text-slate-800 hover:bg-slate-50">Upload photo</label>
                            <input id="profile_photo" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" class="sr-only">
                            <p class="text-center text-[10px] text-slate-500">JPEG, PNG, or GIF up to 2 MB.</p>
                            @error('profile_photo') <p class="text-center text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label for="username" class="mb-1 block text-[11px] font-medium text-slate-800">Username</label>
                                <input id="username" name="username" value="{{ old('settings_modal') === 'account' ? old('username') : $username }}" required maxlength="50" autocomplete="username" class="h-9 w-full rounded-md border border-slate-200 px-3 text-[12px] shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                @error('username') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="account_email" class="mb-1 block text-[11px] font-medium text-slate-800">Email address</label>
                                <input id="account_email" type="email" name="email" value="{{ old('settings_modal') === 'account' ? old('email') : $user->email }}" required maxlength="255" autocomplete="email" class="h-9 w-full rounded-md border border-slate-200 px-3 text-[12px] shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                @error('email') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="space-y-2 border-t border-[#E5E7EB] px-6 py-4">
                    <button type="submit" class="h-10 w-full rounded-md bg-blue-600 text-[12px] font-medium text-white hover:bg-blue-700">Save Changes</button>
                    <button type="button" data-close-modal class="h-10 w-full rounded-md border border-slate-200 text-[12px] text-slate-800 hover:bg-slate-50">Cancel</button>
                </footer>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-open-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.dataset.openModal);
                modal?.classList.replace('hidden', 'flex');
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = button.closest('[data-modal]');
                modal?.classList.replace('flex', 'hidden');
            });
        });

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.replace('flex', 'hidden');
                }
            });
        });

        const modalToReopen = @json(old('settings_modal'));
        if (modalToReopen === 'profile') {
            document.getElementById('editProfileModal')?.classList.replace('hidden', 'flex');
        } else if (modalToReopen === 'account') {
            document.getElementById('editAccountModal')?.classList.replace('hidden', 'flex');
        }

        const photoInput = document.getElementById('profile_photo');
        const photoPreview = document.getElementById('profilePhotoPreview');
        const photoInitials = document.getElementById('profilePhotoInitials');
        const revealPhotoInitials = (image, initials) => {
            if (!image || !initials) {
                return;
            }
            image.classList.add('hidden');
            initials.classList.remove('hidden');
            initials.classList.add('flex');
        };

        document.getElementById('profileSummaryPhoto')?.addEventListener('error', (event) => {
            revealPhotoInitials(event.currentTarget, document.getElementById('profileSummaryInitials'));
        });
        photoPreview?.addEventListener('error', () => revealPhotoInitials(photoPreview, photoInitials));

        photoInput?.addEventListener('change', () => {
            const photo = photoInput.files?.[0];
            if (!photo) {
                return;
            }
            if (photo.size > 2 * 1024 * 1024) {
                photoInput.value = '';
                photoInput.setCustomValidity('Choose a photo no larger than 2 MB.');
                photoInput.reportValidity();
                return;
            }
            photoInput.setCustomValidity('');
            const reader = new FileReader();
            reader.addEventListener('load', () => {
                photoPreview.src = reader.result;
                photoPreview.classList.remove('hidden');
                photoInitials.classList.add('hidden');
                photoInitials.classList.remove('flex');
            });
            reader.readAsDataURL(photo);
        });
    </script>
@endpush
