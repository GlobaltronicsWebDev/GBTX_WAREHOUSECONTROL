@extends('layouts.operations-dashboard')

@section('title', 'Company Settings')
@section('breadcrumb', 'Companies')

@section('content')
    @php
        $companies = [
            ['name' => 'DB International Sales & Services, Inc.', 'code' => 'DBI'],
            ['name' => 'General', 'code' => 'GN'],
            ['name' => 'GGC Foundation', 'code' => 'GGC'],
            ['name' => 'Globaltronics, Inc.', 'code' => 'GLOBAL'],
            ['name' => 'Glory International Technology Inc.', 'code' => 'GLORY'],
            ['name' => 'Nutricetical', 'code' => 'NT'],
            ['name' => 'Organic Coconut Association of The Philippines', 'code' => 'OCAP'],
        ];
    @endphp

    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-[18px] font-semibold leading-6 text-slate-900">Company</h1>
                <span class="border border-[#E5E7EB] bg-[#F8F9FA] px-2 py-1 text-[9px] font-medium uppercase tracking-wide text-slate-500">Preview records</span>
            </div>
            <button type="button" disabled title="Company management is not connected to application data" class="h-8 cursor-not-allowed border border-[#E5E7EB] px-3 text-[11px] text-slate-400">Add Company</button>
        </section>

        <section class="border border-[#E5E7EB] bg-white">
            <div class="flex flex-col gap-2 border-b border-[#E5E7EB] px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[11px] text-slate-500">Company directory layout preview; entries are not stored in the application database.</p>
                <label class="flex h-8 items-center gap-2 border border-[#E5E7EB] px-2 text-slate-400">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="6.5"/><path stroke-linecap="round" d="m16 16 5 5"/>
                    </svg>
                    <span class="sr-only">Search companies</span>
                    <input id="companySearch" type="search" placeholder="Search..." class="w-36 border-0 p-0 text-[11px] text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0">
                </label>
            </div>

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
                    <tbody id="companyRows" class="divide-y divide-[#E5E7EB]">
                        @foreach ($companies as $index => $company)
                            <tr data-company-row data-company-name="{{ strtolower($company['name'].' '.$company['code']) }}" class="h-[66px] text-slate-700">
                                <td class="px-3 font-mono tabular-nums text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-10 w-16 shrink-0 items-center justify-center border border-[#E5E7EB] bg-[#F8F9FA] text-[11px] font-medium text-slate-500">{{ $company['code'] }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-[11px] font-medium text-slate-800">{{ $company['name'] }}</p>
                                            <p class="text-[9px] text-slate-400">{{ $company['code'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 text-slate-400">—</td>
                                <td class="px-3">
                                    <span class="border border-[#E5E7EB] bg-[#F8F9FA] px-2 py-1 text-[9px] uppercase tracking-wide text-slate-500">Preview</span>
                                </td>
                                <td class="px-3 text-right">
                                    <button type="button" disabled title="Preview only" class="cursor-not-allowed px-1 text-slate-300" aria-label="Edit preview company">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 5 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/></svg>
                                    </button>
                                    <button type="button" disabled title="Preview only" class="cursor-not-allowed px-1 text-slate-300" aria-label="Delete preview company">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        <tr id="companySearchEmpty" class="hidden">
                            <td colspan="5" class="px-3 py-8 text-center text-[11px] text-slate-500">No matching preview companies.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const companySearch = document.getElementById('companySearch');
        const companyRows = [...document.querySelectorAll('[data-company-row]')];
        const companySearchEmpty = document.getElementById('companySearchEmpty');

        companySearch.addEventListener('input', () => {
            const query = companySearch.value.trim().toLowerCase();
            let visibleRows = 0;

            companyRows.forEach((row) => {
                const matches = row.dataset.companyName.includes(query);
                row.classList.toggle('hidden', !matches);
                visibleRows += matches ? 1 : 0;
            });

            companySearchEmpty.classList.toggle('hidden', visibleRows > 0);
        });
    </script>
@endpush
