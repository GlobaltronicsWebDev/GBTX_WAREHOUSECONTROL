@extends('layouts.operations-dashboard')

@section('title', 'Departments')
@section('breadcrumb', 'Department')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-[16px] font-semibold text-slate-900">Department</h1>
                <span class="text-[10px] text-slate-500">{{ number_format($departments->total()) }} records</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <time id="dashboardDate" class="text-[9px] uppercase tracking-wide text-slate-500">Loading date</time>
                <time id="dashboardClock" class="font-mono text-[10px] font-medium text-slate-700">--:--:--</time>
                <button type="button" data-open-modal="createDepartmentModal" class="flex h-8 w-8 items-center justify-center bg-[#2563EB] text-lg text-white hover:bg-blue-700" aria-label="Add department">+</button>
            </div>
        </section>

        @if (session('status'))
            <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[11px] text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="border border-[#E5E7EB] bg-white">
            <form method="GET" action="{{ route('admin.settings.departments') }}" class="flex flex-wrap items-center justify-end gap-2 border-b border-[#E5E7EB] px-3 py-2.5">
                <label class="sr-only" for="departmentSearch">Search departments</label>
                <input id="departmentSearch" type="search" name="search" value="{{ $search }}" placeholder="Search..." class="h-8 w-44 border border-[#E5E7EB] px-2 text-[11px] outline-none focus:border-[#2563EB]">
                <label class="sr-only" for="departmentStatus">Department status</label>
                <select id="departmentStatus" name="status" class="h-8 border border-[#E5E7EB] bg-white px-2 text-[11px]">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="h-8 border border-[#E5E7EB] px-3 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Search</button>
                @if ($search !== '' || $status !== '')
                    <a href="{{ route('admin.settings.departments') }}" class="text-[10px] text-slate-500 underline">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse text-left text-[11px]">
                    <thead class="h-9 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium text-slate-500">
                        <tr><th class="w-12 px-3">#</th><th class="px-3">Name</th><th class="px-3">Status</th><th class="px-3">Users</th><th class="w-20 px-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($departments as $department)
                            <tr class="h-16 text-slate-700">
                                <td class="px-3 font-mono text-slate-500">{{ $departments->firstItem() + $loop->index }}</td>
                                <td class="px-3 font-medium text-slate-900">{{ $department->name }}</td>
                                <td class="px-3"><span class="rounded-full px-2 py-1 text-[9px] {{ $department->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $department->is_active ? '● Active' : 'Inactive' }}</span></td>
                                <td class="px-3 text-slate-500">{{ $department->users_count ? $department->users_count.' '.\Illuminate\Support\Str::plural('User', $department->users_count) : 'No Users' }}</td>
                                <td class="px-3 text-right">
                                    <button type="button" data-open-modal="editDepartmentModal-{{ $department->id }}" class="px-1 text-slate-500 hover:text-[#2563EB]" aria-label="Edit {{ $department->name }}">✎</button>
                                    <form method="POST" action="{{ route('admin.settings.departments.destroy', $department) }}" class="inline" onsubmit="return confirm('Delete this department? Assigned users will become unassigned.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-1 text-slate-500 hover:text-red-600" aria-label="Delete {{ $department->name }}">⌫</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-10 text-center text-[11px] text-slate-500">{{ $search !== '' || $status !== '' ? 'No departments match these filters.' : 'No departments have been added yet.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($departments->hasPages())
                <div class="border-t border-[#E5E7EB] px-3 py-2">{{ $departments->links() }}</div>
            @endif
        </section>
    </div>

    <div id="createDepartmentModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="createDepartmentTitle">
        <div class="w-full max-w-md border border-[#E5E7EB] bg-white">
            <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3"><h2 id="createDepartmentTitle" class="text-[14px] font-semibold">Add Department</h2><button type="button" data-close-modal aria-label="Close" class="px-2 text-lg text-slate-500">×</button></div>
            <form method="POST" action="{{ route('admin.settings.departments.store') }}" class="space-y-3 p-4">
                @csrf
                <input type="hidden" name="department_id" value="">
                <label class="block text-[11px] font-medium text-slate-600">Name<input name="name" value="{{ old('department_id') ? '' : old('name') }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                <label class="flex items-center gap-2 text-[11px]"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('department_id') ? true : old('is_active', true))> Active</label>
                <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3"><button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px]">Cancel</button><button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white">Save Department</button></div>
            </form>
        </div>
    </div>
    @foreach ($departments as $department)
        <div id="editDepartmentModal-{{ $department->id }}" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="editDepartmentTitle-{{ $department->id }}">
            <div class="w-full max-w-md border border-[#E5E7EB] bg-white">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3"><h2 id="editDepartmentTitle-{{ $department->id }}" class="text-[14px] font-semibold">Edit Department</h2><button type="button" data-close-modal aria-label="Close" class="px-2 text-lg text-slate-500">×</button></div>
                <form method="POST" action="{{ route('admin.settings.departments.update', $department) }}" class="space-y-3 p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="department_id" value="{{ $department->id }}">
                    <label class="block text-[11px] font-medium text-slate-600">Name<input name="name" value="{{ old('department_id') == $department->id ? old('name') : $department->name }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                    <label class="flex items-center gap-2 text-[11px]"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('department_id') == $department->id ? old('is_active', $department->is_active) : $department->is_active)> Active</label>
                    <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3"><button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px]">Cancel</button><button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white">Save Changes</button></div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    @if ($errors->any())
        <script>
            const failedDepartmentId = @json(old('department_id'));
            document.getElementById(failedDepartmentId ? `editDepartmentModal-${failedDepartmentId}` : 'createDepartmentModal')?.classList.replace('hidden', 'flex');
        </script>
    @endif
@endpush
