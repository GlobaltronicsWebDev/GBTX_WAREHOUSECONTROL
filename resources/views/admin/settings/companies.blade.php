@extends('layouts.operations-dashboard')

@section('title', 'Company Settings')
@section('breadcrumb', 'Companies')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-[18px] font-semibold leading-6 text-slate-900">Company</h1>
                <span class="text-[10px] text-slate-500">{{ number_format($companies->total()) }} records</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <time id="dashboardDate" class="text-[9px] uppercase tracking-wide text-slate-500">Loading date</time>
                <time id="dashboardClock" class="tabular-nums font-mono text-[10px] font-medium text-slate-700">--:--:--</time>
                <button type="button" data-open-modal="createCompanyModal" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Add Company</button>
            </div>
        </section>

        <section class="border border-[#E5E7EB] bg-white">
            <form method="GET" action="{{ route('admin.settings.companies') }}" class="flex flex-col gap-2 border-b border-[#E5E7EB] px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[11px] text-slate-500">Company directory · changes are stored in the application database</p>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="sr-only" for="companySearch">Search companies</label>
                    <input id="companySearch" type="search" name="search" value="{{ $search }}" placeholder="Search..." class="h-8 w-40 border border-[#E5E7EB] px-2 text-[11px] outline-none focus:border-[#2563EB]">
                    <label class="sr-only" for="companyStatus">Company status</label>
                    <select id="companyStatus" name="status" class="h-8 border border-[#E5E7EB] bg-white px-2 text-[11px] outline-none focus:border-[#2563EB]">
                        <option value="">All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                    <button type="submit" class="h-8 border border-[#E5E7EB] px-3 text-[11px] text-slate-700 hover:bg-[#F8F9FA]">Search</button>
                    @if ($search !== '' || in_array($status, ['active', 'inactive'], true))
                        <a href="{{ route('admin.settings.companies') }}" class="text-[10px] text-slate-500 underline">Clear</a>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-left text-[11px]">
                    <thead class="h-9 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium text-slate-500">
                        <tr>
                            <th class="w-10 px-3">#</th>
                            <th class="px-3">Company</th>
                            <th class="px-3">Contact</th>
                            <th class="px-3">Status</th>
                            <th class="w-20 px-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($companies as $company)
                            <tr class="h-[66px] text-slate-700">
                                <td class="px-3 font-mono tabular-nums text-slate-500">{{ $companies->firstItem() + $loop->index }}</td>
                                <td class="px-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-10 w-16 shrink-0 items-center justify-center border border-[#E5E7EB] bg-[#F8F9FA] text-[11px] font-medium text-slate-500">{{ $company->code }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-[11px] font-medium text-slate-800">{{ $company->name }}</p>
                                            <p class="text-[9px] text-slate-400">{{ $company->code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3">
                                    <p class="text-[11px] text-slate-700">{{ $company->contact_phone ?: '—' }}</p>
                                    <p class="text-[9px] text-slate-500">{{ $company->contact_email ?: '—' }}</p>
                                </td>
                                <td class="px-3">
                                    <span class="border px-2 py-1 text-[9px] uppercase tracking-wide {{ $company->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-[#E5E7EB] bg-[#F8F9FA] text-slate-500' }}">
                                        {{ $company->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-3 text-right">
                                    <button type="button" data-open-modal="editCompanyModal-{{ $company->id }}" class="px-1 text-slate-500 hover:text-[#2563EB]" aria-label="Edit {{ $company->name }}">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 5 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.settings.companies.destroy', $company) }}" class="inline" onsubmit="return confirm('Delete this company record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-1 text-slate-500 hover:text-red-600" aria-label="Delete {{ $company->name }}">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-10 text-center text-[11px] text-slate-500">
                                    @if ($search !== '' || in_array($status, ['active', 'inactive'], true))
                                        No companies match these filters.
                                    @else
                                        No companies have been added yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($companies->hasPages())
                <div class="border-t border-[#E5E7EB] px-3 py-2">{{ $companies->links() }}</div>
            @endif
        </section>
    </div>

    <div id="createCompanyModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="createCompanyTitle">
        <div class="w-full max-w-xl border border-[#E5E7EB] bg-white">
            <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3">
                <h2 id="createCompanyTitle" class="text-[15px] font-semibold text-slate-900">Add Company</h2>
                <button type="button" data-close-modal class="px-2 text-[18px] leading-none text-slate-500 hover:bg-[#F8F9FA]" aria-label="Close">×</button>
            </div>
            <form method="POST" action="{{ route('admin.settings.companies.store') }}" class="space-y-3 p-4">
                @csrf
                <input type="hidden" name="company_id" value="">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Company name
                        <input name="name" value="{{ old('company_id') ? '' : old('name') }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </label>
                    <label class="text-[11px] font-medium text-slate-600">Company code
                        <input name="code" value="{{ old('company_id') ? '' : old('code') }}" required maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9_-]*" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] uppercase outline-none focus:border-[#2563EB]">
                    </label>
                    <label class="text-[11px] font-medium text-slate-600">Contact phone
                        <input name="contact_phone" value="{{ old('company_id') ? '' : old('contact_phone') }}" maxlength="50" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </label>
                    <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Contact email
                        <input type="email" name="contact_email" value="{{ old('company_id') ? '' : old('contact_email') }}" maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </label>
                    <label class="flex items-center gap-2 text-[11px] text-slate-700 sm:col-span-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('company_id') ? true : old('is_active', true)) class="h-3.5 w-3.5 accent-[#2563EB]">
                        Active
                    </label>
                </div>
                <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3">
                    <button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px] text-slate-700">Cancel</button>
                    <button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Save Company</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($companies as $company)
        <div id="editCompanyModal-{{ $company->id }}" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="editCompanyTitle-{{ $company->id }}">
            <div class="w-full max-w-xl border border-[#E5E7EB] bg-white">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3">
                    <h2 id="editCompanyTitle-{{ $company->id }}" class="text-[15px] font-semibold text-slate-900">Edit Company</h2>
                    <button type="button" data-close-modal class="px-2 text-[18px] leading-none text-slate-500 hover:bg-[#F8F9FA]" aria-label="Close">×</button>
                </div>
                <form method="POST" action="{{ route('admin.settings.companies.update', $company) }}" class="space-y-3 p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Company name
                            <input name="name" value="{{ old('company_id') == $company->id ? old('name') : $company->name }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">Company code
                            <input name="code" value="{{ old('company_id') == $company->id ? old('code') : $company->code }}" required maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9_-]*" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] uppercase outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600">Contact phone
                            <input name="contact_phone" value="{{ old('company_id') == $company->id ? old('contact_phone') : $company->contact_phone }}" maxlength="50" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Contact email
                            <input type="email" name="contact_email" value="{{ old('company_id') == $company->id ? old('contact_email') : $company->contact_email }}" maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px] outline-none focus:border-[#2563EB]">
                        </label>
                        <label class="flex items-center gap-2 text-[11px] text-slate-700 sm:col-span-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('company_id') == $company->id ? old('is_active', $company->is_active) : $company->is_active) class="h-3.5 w-3.5 accent-[#2563EB]">
                            Active
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3">
                        <button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px] text-slate-700">Cancel</button>
                        <button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
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

        @if ($errors->any())
            const failedCompanyId = @json(old('company_id'));
            const failedModalId = failedCompanyId ? `editCompanyModal-${failedCompanyId}` : 'createCompanyModal';
            document.getElementById(failedModalId)?.classList.replace('hidden', 'flex');
        @endif
    </script>
@endpush
