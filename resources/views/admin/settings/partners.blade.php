@extends('layouts.operations-dashboard')

@section('title', 'Supplier/Client')
@section('breadcrumb', 'Supplier/Client')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3"><h1 class="text-[16px] font-semibold text-slate-900">Supplier/Client</h1><span class="text-[10px] text-slate-500">{{ number_format($partners->total()) }} records</span></div>
            <div class="flex flex-wrap items-center gap-3"><time id="dashboardDate" class="text-[9px] uppercase tracking-wide text-slate-500">Loading date</time><time id="dashboardClock" class="font-mono text-[10px] font-medium text-slate-700">--:--:--</time><button type="button" data-open-modal="createPartnerModal" class="flex h-8 w-8 items-center justify-center bg-[#2563EB] text-lg text-white hover:bg-blue-700" aria-label="Add supplier or client">+</button></div>
        </section>

        @if (session('status')) <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[11px] text-red-700">{{ $errors->first() }}</div> @endif

        <section class="border border-[#E5E7EB] bg-white">
            <form method="GET" action="{{ route('admin.settings.partners') }}" class="flex flex-wrap items-center justify-end gap-2 border-b border-[#E5E7EB] px-3 py-2.5">
                <label class="sr-only" for="partnerSearch">Search suppliers and clients</label>
                <input id="partnerSearch" type="search" name="search" value="{{ $search }}" placeholder="Search..." class="h-8 w-44 border border-[#E5E7EB] px-2 text-[11px]">
                <label class="sr-only" for="partnerType">Partner type</label>
                <select id="partnerType" name="type" class="h-8 border border-[#E5E7EB] bg-white px-2 text-[11px]"><option value="">All types</option><option value="supplier" @selected($type === 'supplier')>Supplier</option><option value="client" @selected($type === 'client')>Client</option></select>
                <label class="sr-only" for="partnerStatus">Partner status</label>
                <select id="partnerStatus" name="status" class="h-8 border border-[#E5E7EB] bg-white px-2 text-[11px]"><option value="">All statuses</option><option value="active" @selected($status === 'active')>Active</option><option value="inactive" @selected($status === 'inactive')>Inactive</option></select>
                <button type="submit" class="h-8 border border-[#E5E7EB] px-3 text-[11px] hover:bg-[#F8F9FA]">Search</button>
                @if ($search !== '' || $status !== '' || $type !== '') <a href="{{ route('admin.settings.partners') }}" class="text-[10px] text-slate-500 underline">Clear</a> @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] border-collapse text-left text-[11px]">
                    <thead class="h-9 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium text-slate-500"><tr><th class="w-12 px-3">#</th><th class="px-3">Name</th><th class="px-3">Type</th><th class="px-3">Category/Address</th><th class="px-3">Status</th><th class="w-20 px-3 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($partners as $partner)
                            <tr class="h-16 text-slate-700">
                                <td class="px-3 font-mono text-slate-500">{{ $partners->firstItem() + $loop->index }}</td>
                                <td class="px-3 font-medium text-slate-900">{{ $partner->name }}</td>
                                <td class="px-3 capitalize">{{ $partner->type }}</td>
                                <td class="max-w-[420px] px-3"><p class="truncate">{{ $partner->category ?: '—' }}</p><p class="truncate text-[10px] text-slate-500">{{ $partner->address ?: '—' }}</p></td>
                                <td class="px-3"><span class="rounded-full px-2 py-1 text-[9px] {{ $partner->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $partner->is_active ? '● Active' : 'Inactive' }}</span></td>
                                <td class="px-3 text-right">
                                    <button type="button" data-open-modal="editPartnerModal-{{ $partner->id }}" class="px-1 text-slate-500 hover:text-[#2563EB]" aria-label="Edit {{ $partner->name }}">✎</button>
                                    <form method="POST" action="{{ route('admin.settings.partners.destroy', $partner) }}" class="inline" onsubmit="return confirm('Delete this supplier/client record?')">@csrf @method('DELETE')<button type="submit" class="px-1 text-slate-500 hover:text-red-600" aria-label="Delete {{ $partner->name }}">⌫</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-10 text-center text-[11px] text-slate-500">{{ $search !== '' || $status !== '' || $type !== '' ? 'No suppliers or clients match these filters.' : 'No suppliers or clients have been added yet.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($partners->hasPages()) <div class="border-t border-[#E5E7EB] px-3 py-2">{{ $partners->links() }}</div> @endif
        </section>
    </div>

    <div id="createPartnerModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="createPartnerTitle">
        <div class="w-full max-w-xl border border-[#E5E7EB] bg-white">
            <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3"><h2 id="createPartnerTitle" class="text-[14px] font-semibold">Add Supplier/Client</h2><button type="button" data-close-modal aria-label="Close" class="px-2 text-lg text-slate-500">×</button></div>
            <form method="POST" action="{{ route('admin.settings.partners.store') }}" class="space-y-3 p-4">
                @csrf
                <input type="hidden" name="partner_id" value="">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Name<input name="name" value="{{ old('partner_id') ? '' : old('name') }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                    <label class="text-[11px] font-medium text-slate-600">Type<select name="type" required class="mt-1 h-9 w-full border border-[#E5E7EB] bg-white px-2 text-[12px]"><option value="supplier" @selected(old('partner_id') ? true : old('type', 'supplier') === 'supplier')>Supplier</option><option value="client" @selected(old('partner_id') ? false : old('type') === 'client')>Client</option></select></label>
                    <label class="text-[11px] font-medium text-slate-600">Category<input name="category" value="{{ old('partner_id') ? '' : old('category') }}" maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                    <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Address<textarea name="address" rows="2" maxlength="2000" class="mt-1 w-full border border-[#E5E7EB] px-2 py-2 text-[12px]">{{ old('partner_id') ? '' : old('address') }}</textarea></label>
                    <label class="flex items-center gap-2 text-[11px] sm:col-span-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('partner_id') ? true : old('is_active', true))> Active</label>
                </div>
                <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3"><button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px]">Cancel</button><button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white">Save Record</button></div>
            </form>
        </div>
    </div>
    @foreach ($partners as $partner)
        <div id="editPartnerModal-{{ $partner->id }}" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="editPartnerTitle-{{ $partner->id }}">
            <div class="w-full max-w-xl border border-[#E5E7EB] bg-white">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3"><h2 id="editPartnerTitle-{{ $partner->id }}" class="text-[14px] font-semibold">Edit Supplier/Client</h2><button type="button" data-close-modal aria-label="Close" class="px-2 text-lg text-slate-500">×</button></div>
                <form method="POST" action="{{ route('admin.settings.partners.update', $partner) }}" class="space-y-3 p-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Name<input name="name" value="{{ old('partner_id') == $partner->id ? old('name') : $partner->name }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                        <label class="text-[11px] font-medium text-slate-600">Type<select name="type" required class="mt-1 h-9 w-full border border-[#E5E7EB] bg-white px-2 text-[12px]"><option value="supplier" @selected(old('partner_id') == $partner->id ? old('type', $partner->type) === 'supplier' : $partner->type === 'supplier')>Supplier</option><option value="client" @selected(old('partner_id') == $partner->id ? old('type', $partner->type) === 'client' : $partner->type === 'client')>Client</option></select></label>
                        <label class="text-[11px] font-medium text-slate-600">Category<input name="category" value="{{ old('partner_id') == $partner->id ? old('category') : $partner->category }}" maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                        <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Address<textarea name="address" rows="2" maxlength="2000" class="mt-1 w-full border border-[#E5E7EB] px-2 py-2 text-[12px]">{{ old('partner_id') == $partner->id ? old('address') : $partner->address }}</textarea></label>
                        <label class="flex items-center gap-2 text-[11px] sm:col-span-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('partner_id') == $partner->id ? old('is_active', $partner->is_active) : $partner->is_active)> Active</label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3"><button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px]">Cancel</button><button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white">Save Changes</button></div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    @if ($errors->any())
        <script>
            const failedPartnerId = @json(old('partner_id'));
            document.getElementById(failedPartnerId ? `editPartnerModal-${failedPartnerId}` : 'createPartnerModal')?.classList.replace('hidden', 'flex');
        </script>
    @endif
@endpush
