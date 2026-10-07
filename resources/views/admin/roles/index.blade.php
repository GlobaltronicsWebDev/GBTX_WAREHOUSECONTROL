@extends('layouts.admin')

@section('title', 'Roles & Clearances')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
    <div>
        <div class="flex items-center gap-2 text-xs font-mono-code text-blue-600 mb-1">
            <span>ADMIN CONSOLE</span>
            <span>•</span>
            <span>ROLE-BASED ACCESS CONTROL (RBAC)</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Roles & Clearances</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Define system roles, clearance tiers, badge styling, and operational authorizations for warehouse users.
        </p>
    </div>

    <!-- Create Role Button -->
    <div>
        <button type="button" onclick="openCreateRoleModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.01]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Add New Role</span>
        </button>
    </div>
</div>

<!-- ROLES CARDS GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach ($roles as $role)
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

        <div class="glass-card bg-white rounded-2xl p-5 sm:p-6 flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition-all group shadow-sm border border-slate-200">
            <div>
                <!-- Top Row: Badge & System Indicator -->
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border {{ $colorClasses }}">
                        {{ $role->name }}
                    </span>

                    @if ($role->is_system)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-slate-100 text-slate-700 border border-slate-200" title="Core System Role - Cannot be removed">
                            <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            SYSTEM
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono-code font-bold bg-blue-50 text-blue-600 border border-blue-200">
                            CUSTOM
                        </span>
                    @endif
                </div>

                <!-- Slug and Assigned Count -->
                <div class="flex items-center justify-between text-xs text-slate-500 mb-2 font-mono-code">
                    <span>slug: <strong class="text-slate-800">{{ $role->slug }}</strong></span>
                    <span class="text-slate-700 font-semibold">{{ $role->users_count }} {{ Str::plural('account', $role->users_count) }}</span>
                </div>

                <!-- Description -->
                <p class="text-xs text-slate-600 leading-relaxed min-h-[40px]">
                    {{ $role->description ?: 'No operational scope description provided.' }}
                </p>
            </div>

            <!-- Footer: Actions -->
            <div class="pt-4 mt-4 border-t border-slate-200 flex items-center justify-between gap-2">
                <a href="{{ route('admin.users.index', ['role' => $role->id]) }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 group/link">
                    <span>View Users ({{ $role->users_count }})</span>
                    <svg class="w-3.5 h-3.5 group-hover/link:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <div class="flex items-center gap-1.5">
                    <!-- Edit Role -->
                    <button 
                        type="button" 
                        onclick="openEditRoleModal({{ json_encode([
                            'id' => $role->id,
                            'name' => $role->name,
                            'slug' => $role->slug,
                            'badge_color' => $role->badge_color,
                            'description' => $role->description,
                            'is_system' => $role->is_system,
                        ]) }})" 
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" 
                        title="Edit role details"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>

                    <!-- Delete Role -->
                    @if ($role->is_system)
                        <button type="button" disabled class="p-1.5 rounded-lg bg-slate-50 text-slate-300 border border-slate-200 cursor-not-allowed" title="System roles cannot be deleted">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    @else
                        <button 
                            type="button" 
                            onclick="openDeleteRoleModal({{ json_encode([
                                'id' => $role->id,
                                'name' => $role->name,
                                'users_count' => $role->users_count,
                            ]) }})" 
                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-700 transition-colors" 
                            title="Delete custom role"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('modals')
<!-- ========================================== -->
<!-- MODAL: ADD NEW ROLE                        -->
<!-- ========================================== -->
<div id="createRoleModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden animate-fadeIn">
    <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-7">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Add New Security Role</h3>
                    <p class="text-xs text-slate-500">Create a role and assign clearances</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateRoleModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.roles.store') }}" class="mt-5 space-y-4">
            @csrf

            <!-- Role Name -->
            <div>
                <label for="create_role_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Role Title <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="create_role_name" 
                    required 
                    placeholder="e.g. Quality Assurance Inspector" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Badge Color Choice -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Theme Badge Color <span class="text-amber-500">*</span>
                </label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['blue' => 'Blue', 'amber' => 'Amber', 'emerald' => 'Emerald', 'purple' => 'Purple', 'sky' => 'Sky', 'indigo' => 'Indigo', 'rose' => 'Rose', 'teal' => 'Teal'] as $val => $label)
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 cursor-pointer text-xs">
                            <input type="radio" name="badge_color" value="{{ $val }}" {{ $val === 'blue' ? 'checked' : '' }} class="text-blue-600 focus:ring-0">
                            <span class="text-[11px] text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="create_role_description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Operational Description
                </label>
                <textarea 
                    name="description" 
                    id="create_role_description" 
                    rows="3" 
                    placeholder="Describe role responsibilities, warehouse bay coverage, or permissions..." 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                <button type="button" onclick="closeCreateRoleModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20">
                    Create Role
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: EDIT ROLE                           -->
<!-- ========================================== -->
<div id="editRoleModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden animate-fadeIn">
    <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-7">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Edit Role Configuration</h3>
                    <p class="text-xs text-slate-500">Update role title, badge theme, or scope</p>
                </div>
            </div>
            <button type="button" onclick="closeEditRoleModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editRoleForm" method="POST" action="" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <!-- Role Name -->
            <div>
                <label for="edit_role_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Role Title <span class="text-amber-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="edit_role_name" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Badge Color Choice -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Theme Badge Color <span class="text-amber-500">*</span>
                </label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['blue' => 'Blue', 'amber' => 'Amber', 'emerald' => 'Emerald', 'purple' => 'Purple', 'sky' => 'Sky', 'indigo' => 'Indigo', 'rose' => 'Rose', 'teal' => 'Teal'] as $val => $label)
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 cursor-pointer text-xs">
                            <input type="radio" name="badge_color" value="{{ $val }}" id="edit_color_{{ $val }}" class="text-blue-600 focus:ring-0">
                            <span class="text-[11px] text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="edit_role_description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Operational Description
                </label>
                <textarea 
                    name="description" 
                    id="edit_role_description" 
                    rows="3" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-xs focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                <button type="button" onclick="closeEditRoleModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-500/20">
                    Save Changes
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DELETE ROLE CONFIRMATION            -->
<!-- ========================================== -->
<div id="deleteRoleModal" data-modal class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden animate-fadeIn">
    <div class="relative w-full max-w-md rounded-3xl bg-white border border-rose-200 shadow-2xl p-6 sm:p-7">
        
        <div class="flex items-center gap-3.5 pb-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Delete Role</h3>
                <p class="text-xs text-rose-600">RBAC Clearance Removal</p>
            </div>
        </div>

        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-2">
            Are you sure you want to delete the role <strong id="deleteRoleName" class="text-slate-900"></strong>?
        </p>

        <div id="deleteRoleUserWarning" class="hidden mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
            <span class="font-bold">Attention:</span> There are <span id="deleteRoleUserCount" class="font-bold font-mono-code"></span> user account(s) currently assigned to this role. You must reassign or remove them before deleting this role.
        </div>

        <form id="deleteRoleForm" method="POST" action="" class="mt-5 flex items-center justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteRoleModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                Cancel
            </button>
            <button id="deleteRoleSubmitBtn" type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/20">
                Confirm & Delete
            </button>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateRoleModal() {
        document.getElementById('createRoleModal').classList.remove('hidden');
    }

    function closeCreateRoleModal() {
        document.getElementById('createRoleModal').classList.add('hidden');
    }

    function openEditRoleModal(role) {
        document.getElementById('edit_role_name').value = role.name;
        document.getElementById('edit_role_description').value = role.description || '';
        document.getElementById('editRoleForm').action = '/admin/roles/' + role.id;

        const radio = document.getElementById('edit_color_' + role.badge_color);
        if (radio) {
            radio.checked = true;
        }

        document.getElementById('editRoleModal').classList.remove('hidden');
    }

    function closeEditRoleModal() {
        document.getElementById('editRoleModal').classList.add('hidden');
    }

    function openDeleteRoleModal(role) {
        document.getElementById('deleteRoleName').textContent = role.name;
        document.getElementById('deleteRoleForm').action = '/admin/roles/' + role.id;
        
        const warning = document.getElementById('deleteRoleUserWarning');
        const countSpan = document.getElementById('deleteRoleUserCount');
        const submitBtn = document.getElementById('deleteRoleSubmitBtn');

        if (role.users_count > 0) {
            countSpan.textContent = role.users_count;
            warning.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        document.getElementById('deleteRoleModal').classList.remove('hidden');
    }

    function closeDeleteRoleModal() {
        document.getElementById('deleteRoleModal').classList.add('hidden');
    }
</script>
@endpush
