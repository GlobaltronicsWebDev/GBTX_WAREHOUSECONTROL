@extends('layouts.admin')

@section('title', 'User Accounts & Credentials')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
    <div>
        <div class="flex items-center gap-2 text-xs font-mono-code text-blue-600 mb-1">
            <span>ADMIN CONSOLE</span>
            <span>•</span>
            <span>CREDENTIAL & USER MANAGEMENT</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">User Accounts & Roles</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Create user credentials, assign warehouse department roles, update clearances, or remove personnel accounts.
        </p>
    </div>

    <!-- Create User Button -->
    <div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.01]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Create New User</span>
        </button>
    </div>
</div>

<!-- FILTER & SEARCH TOOLBAR -->
<div class="glass-panel bg-white p-4 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-3 shadow-sm border border-slate-200">
    <form method="GET" action="{{ route('admin.users.index') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
        
        <!-- Search Input -->
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Search user by name or email address..." 
                class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all"
            />
        </div>

        <!-- Role Filter Dropdown -->
        <div class="w-full sm:w-60">
            <select name="role" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Roles ({{ $roles->count() }})</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ $selectedRole == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs sm:text-sm font-semibold transition-colors border border-slate-200">
            Filter
        </button>

        @if ($search || $selectedRole)
            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold text-center transition-colors">
                Reset
            </a>
        @endif
    </form>
</div>

<!-- USERS DATA TABLE -->
<div class="glass-panel bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead>
                <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50 border-b border-slate-200">
                    <th class="py-3.5 px-4">Account User</th>
                    <th class="py-3.5 px-4">Assigned Roles</th>
                    <th class="py-3.5 px-4">Access Clearance</th>
                    <th class="py-3.5 px-4">Date Added</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        <!-- User Name & Email -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $user->name }}</span>
                                        @if (Auth::id() === $user->id)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono-code font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">YOU</span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-mono-code text-slate-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Roles Badges -->
                        <td class="py-4 px-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($user->roles as $role)
                                    @php
                                        $colorClasses = match($role->badge_color) {
                                            'purple' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'sky' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'rose' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-blue-50 text-blue-700 border-blue-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold border {{ $colorClasses }}">
                                        @if ($role->is_system)
                                            <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        @endif
                                        <span>{{ $role->name }}</span>
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">No role assigned</span>
                                @endforelse
                            </div>
                        </td>

                        <!-- Access Clearance -->
                        <td class="py-4 px-4">
                            @if ($user->isAdmin())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono-code font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    ADMIN CLEARANCE
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono-code font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    STAFF CLEARANCE
                                </span>
                            @endif
                        </td>

                        <!-- Created At -->
                        <td class="py-4 px-4 text-xs font-mono-code text-slate-500">
                            {{ $user->created_at->format('M d, Y') }}
                            <div class="text-[10px] text-slate-400">{{ $user->created_at->format('H:i') }} UTC</div>
                        </td>

                        <!-- Action Buttons -->
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                
                                <!-- Edit Button -->
                                <button 
                                    type="button" 
                                    onclick="openEditModal({{ json_encode([
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'role_ids' => $user->roles->pluck('id')->toArray(),
                                        'is_self' => Auth::id() === $user->id
                                    ]) }})" 
                                    class="p-2 rounded-lg bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 transition-colors" 
                                    title="Edit user credentials and roles"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <!-- Delete Button -->
                                @if (Auth::id() === $user->id)
                                    <button type="button" disabled class="p-2 rounded-lg bg-slate-50 text-slate-300 cursor-not-allowed border border-slate-200" title="You cannot delete your own active account">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @else
                                    <button 
                                        type="button" 
                                        onclick="openDeleteModal({{ json_encode([
                                            'id' => $user->id,
                                            'name' => $user->name,
                                            'email' => $user->email,
                                        ]) }})" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-600 transition-colors" 
                                        title="Delete user account"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                            <p class="font-medium">No user accounts found matching your query.</p>
                            <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 hover:underline mt-2 inline-block font-semibold">Clear filters and show all</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($users->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

@section('modals')
<!-- ========================================== -->
<!-- MODAL: CREATE USER ACCOUNT                 -->
<!-- ========================================== -->
<div id="createUserModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden animate-fadeIn">
    <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Create New User Account</h3>
                    <p class="text-xs text-slate-500">Register warehouse personnel or system administrators</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 space-y-4">
            @csrf

            <!-- Full Name -->
            <div>
                <label for="create_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Full Name <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="create_name" 
                    required 
                    placeholder="e.g. Juan Dela Cruz" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Email Address -->
            <div>
                <label for="create_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Email Address <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="create_email" 
                    required 
                    placeholder="e.g. juan.delacruz@globaltronics.net" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="create_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Initial Password <span class="text-amber-500">*</span>
                    </label>
                    <button type="button" onclick="generatePassword('create_password')" class="text-[11px] text-blue-600 hover:text-blue-700 underline font-mono-code font-semibold">
                        Auto Generate
                    </button>
                </div>
                <div class="relative">
                    <input 
                        type="text" 
                        name="password" 
                        id="create_password" 
                        required 
                        minlength="8" 
                        placeholder="Minimum 8 characters" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                    />
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Credentials can be changed by the user or admin anytime.</p>
            </div>

            <!-- Role Assignment Checkboxes -->
            <div class="pt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Assign Roles & Clearances
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @foreach ($roles as $role)
                        @php
                            $colorClasses = match($role->badge_color) {
                                'purple' => 'text-purple-700 border-purple-200 bg-purple-50',
                                'amber' => 'text-amber-700 border-amber-200 bg-amber-50',
                                'emerald' => 'text-emerald-700 border-emerald-200 bg-emerald-50',
                                'sky' => 'text-sky-700 border-sky-200 bg-sky-50',
                                'indigo' => 'text-indigo-700 border-indigo-200 bg-indigo-50',
                                'rose' => 'text-rose-700 border-rose-200 bg-rose-50',
                                default => 'text-blue-700 border-blue-200 bg-blue-50',
                            };
                        @endphp
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 cursor-pointer transition-colors">
                            <input 
                                type="checkbox" 
                                name="roles[]" 
                                value="{{ $role->id }}" 
                                class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <div class="flex-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $role->name }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] border {{ $colorClasses }} font-semibold">
                                        {{ $role->slug }}
                                    </span>
                                </div>
                                @if ($role->description)
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $role->description }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 transition-all">
                    Create User Account
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: EDIT USER & ROLES                   -->
<!-- ========================================== -->
<div id="editUserModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden animate-fadeIn">
    <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Edit User Credentials & Roles</h3>
                    <p class="text-xs text-slate-500">Modify personnel profile, password, or security clearance</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="editUserForm" method="POST" action="" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <!-- Full Name -->
            <div>
                <label for="edit_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Full Name <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="edit_name" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Email Address -->
            <div>
                <label for="edit_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Email Address <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="edit_email" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <!-- Optional New Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="edit_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Reset Password <span class="text-slate-400 font-normal normal-case">(leave blank to keep current)</span>
                    </label>
                    <button type="button" onclick="generatePassword('edit_password')" class="text-[11px] text-blue-600 hover:text-blue-700 underline font-mono-code font-semibold">
                        Auto Generate
                    </button>
                </div>
                <input 
                    type="text" 
                    name="password" 
                    id="edit_password" 
                    minlength="8" 
                    placeholder="Enter new password to overwrite" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono-code"
                />
            </div>

            <!-- Role Assignment Checkboxes -->
            <div class="pt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Security Roles & Clearance Assignment
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @foreach ($roles as $role)
                        @php
                            $colorClasses = match($role->badge_color) {
                                'purple' => 'text-purple-700 border-purple-200 bg-purple-50',
                                'amber' => 'text-amber-700 border-amber-200 bg-amber-50',
                                'emerald' => 'text-emerald-700 border-emerald-200 bg-emerald-50',
                                'sky' => 'text-sky-700 border-sky-200 bg-sky-50',
                                'indigo' => 'text-indigo-700 border-indigo-200 bg-indigo-50',
                                'rose' => 'text-rose-700 border-rose-200 bg-rose-50',
                                default => 'text-blue-700 border-blue-200 bg-blue-50',
                            };
                        @endphp
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 cursor-pointer transition-colors">
                            <input 
                                type="checkbox" 
                                name="roles[]" 
                                value="{{ $role->id }}" 
                                id="edit_role_{{ $role->id }}" 
                                class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <div class="flex-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $role->name }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] border {{ $colorClasses }} font-semibold">
                                        {{ $role->slug }}
                                    </span>
                                </div>
                                @if ($role->description)
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $role->description }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-amber-500/20 transition-all">
                    Save Changes
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DELETE USER CONFIRMATION            -->
<!-- ========================================== -->
<div id="deleteUserModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden animate-fadeIn">
    <div class="relative w-full max-w-md rounded-3xl bg-white border border-rose-200 shadow-2xl p-6 sm:p-7">
        
        <div class="flex items-center gap-3.5 pb-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Delete User Account</h3>
                <p class="text-xs text-rose-600">Permanent removal of credentials</p>
            </div>
        </div>

        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-2">
            Are you sure you want to permanently delete the account for 
            <strong id="deleteUserName" class="text-slate-900"></strong> 
            (<span id="deleteUserEmail" class="font-mono-code text-slate-500"></span>)?
        </p>

        <p class="text-[11px] text-slate-600 mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
            <strong>Warning:</strong> This action cannot be undone. Associated session tokens and role assignments will be instantly revoked.
        </p>

        <form id="deleteUserForm" method="POST" action="" class="mt-5 flex items-center justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-rose-500/20 transition-all">
                Confirm & Delete
            </button>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }

    function openEditModal(user) {
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_password').value = '';
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;

        // Reset and check assigned role checkboxes
        document.querySelectorAll('#editUserModal input[name="roles[]"]').forEach(cb => {
            cb.checked = user.role_ids.includes(parseInt(cb.value));
        });

        document.getElementById('editUserModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    function openDeleteModal(user) {
        document.getElementById('deleteUserName').textContent = user.name;
        document.getElementById('deleteUserEmail').textContent = user.email;
        document.getElementById('deleteUserForm').action = '/admin/users/' + user.id;
        document.getElementById('deleteUserModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteUserModal').classList.add('hidden');
    }

    function generatePassword(targetInputId) {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
        let pass = '';
        for (let i = 0; i < 14; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById(targetInputId).value = pass;
    }

    // Auto open create modal if targeted by hash
    if (window.location.hash === '#createUser') {
        openCreateModal();
    }
</script>
@endpush
