@extends('layouts.operations-dashboard')

@section('title', 'Warehouse/Location')
@section('breadcrumb', 'Warehouse/Location')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3"><h1 class="text-[16px] font-semibold text-slate-900">Location</h1><span class="text-[10px] text-slate-500">{{ number_format($locations->total()) }} records</span></div>
            <div class="flex flex-wrap items-center gap-3"><time id="dashboardDate" class="text-[9px] uppercase tracking-wide text-slate-500">Loading date</time><time id="dashboardClock" class="font-mono text-[10px] font-medium text-slate-700">--:--:--</time><button type="button" data-open-modal="createLocationModal" class="flex h-8 w-8 items-center justify-center bg-[#2563EB] text-lg text-white hover:bg-blue-700" aria-label="Add location">+</button></div>
        </section>

        @if (session('status')) <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[11px] text-red-700">{{ $errors->first() }}</div> @endif

        <section class="border border-[#E5E7EB] bg-white">
            <form method="GET" action="{{ route('admin.settings.locations') }}" class="flex flex-wrap items-center justify-end gap-2 border-b border-[#E5E7EB] px-3 py-2.5">
                <label class="sr-only" for="locationSearch">Search locations</label>
                <input id="locationSearch" type="search" name="search" value="{{ $search }}" placeholder="Search..." class="h-8 w-44 border border-[#E5E7EB] px-2 text-[11px]">
                <label class="sr-only" for="locationStatus">Location status</label>
                <select id="locationStatus" name="status" class="h-8 border border-[#E5E7EB] bg-white px-2 text-[11px]"><option value="">All statuses</option><option value="active" @selected($status === 'active')>Active</option><option value="inactive" @selected($status === 'inactive')>Inactive</option></select>
                <button type="submit" class="h-8 border border-[#E5E7EB] px-3 text-[11px] hover:bg-[#F8F9FA]">Search</button>
                @if ($search !== '' || $status !== '') <a href="{{ route('admin.settings.locations') }}" class="text-[10px] text-slate-500 underline">Clear</a> @endif
            </form>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-left text-[11px]">
                    <thead class="h-9 border-b border-[#E5E7EB] bg-[#F8F9FA] text-[10px] font-medium text-slate-500"><tr><th class="w-12 px-3">#</th><th class="px-3">Name</th><th class="px-3">Entity</th><th class="px-3">Status</th><th class="px-3">Navigation</th><th class="w-20 px-3 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($locations as $location)
                            <tr class="h-16 text-slate-700">
                                <td class="px-3 font-mono text-slate-500">{{ $locations->firstItem() + $loop->index }}</td>
                                <td class="px-3 font-medium text-slate-900">{{ $location->name }}</td>
                                <td class="px-3">{{ $location->entity }}</td>
                                <td class="px-3"><span class="rounded-full px-2 py-1 text-[9px] {{ $location->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $location->is_active ? '● Active' : 'Inactive' }}</span></td>
                                <td class="px-3"><span class="rounded-full px-2 py-1 text-[9px] {{ $location->navigation_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $location->navigation_enabled ? '● On' : 'Off' }}</span></td>
                                <td class="px-3 text-right">
                                    <button type="button" data-open-modal="editLocationModal-{{ $location->id }}" class="px-1 text-slate-500 hover:text-[#2563EB]" aria-label="Edit {{ $location->name }}">✎</button>
                                    <form method="POST" action="{{ route('admin.settings.locations.destroy', $location) }}" class="inline" onsubmit="return confirm('Delete this warehouse/location?')">@csrf @method('DELETE')<button type="submit" class="px-1 text-slate-500 hover:text-red-600" aria-label="Delete {{ $location->name }}">⌫</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-10 text-center text-[11px] text-slate-500">{{ $search !== '' || $status !== '' ? 'No locations match these filters.' : 'No warehouse locations have been added yet.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($locations->hasPages()) <div class="border-t border-[#E5E7EB] px-3 py-2">{{ $locations->links() }}</div> @endif
        </section>
    </div>

    <div id="createLocationModal" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="createLocationTitle">
        <div class="w-full max-w-md border border-[#E5E7EB] bg-white">
            <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3"><h2 id="createLocationTitle" class="text-[14px] font-semibold">Add Warehouse/Location</h2><button type="button" data-close-modal aria-label="Close" class="px-2 text-lg text-slate-500">×</button></div>
            <form method="POST" action="{{ route('admin.settings.locations.store') }}" class="space-y-3 p-4">
                @csrf
                <input type="hidden" name="location_id" value="">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Name<input name="name" value="{{ old('location_id') ? '' : old('name') }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                    <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Entity<input name="entity" value="{{ old('location_id') ? 'Warehouse' : old('entity', 'Warehouse') }}" required maxlength="100" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                    <label class="flex items-center gap-2 text-[11px]"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('location_id') ? true : old('is_active', true))> Active</label>
                    <label class="flex items-center gap-2 text-[11px]"><input type="hidden" name="navigation_enabled" value="0"><input type="checkbox" name="navigation_enabled" value="1" @checked(old('location_id') ? true : old('navigation_enabled', true))> Show in navigation</label>
                </div>
                <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-3"><button type="button" data-close-modal class="h-8 border border-[#E5E7EB] px-3 text-[11px]">Cancel</button><button type="submit" class="h-8 bg-[#2563EB] px-3 text-[11px] font-medium text-white">Save Location</button></div>
            </form>
        </div>
    </div>
    @foreach ($locations as $location)
        <div id="editLocationModal-{{ $location->id }}" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/30 p-3" role="dialog" aria-modal="true" aria-labelledby="editLocationTitle-{{ $location->id }}">
            <div class="w-full max-w-md border border-[#E5E7EB] bg-white">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3"><h2 id="editLocationTitle-{{ $location->id }}" class="text-[14px] font-semibold">Edit Warehouse/Location</h2><button type="button" data-close-modal aria-label="Close" class="px-2 text-lg text-slate-500">×</button></div>
                <form method="POST" action="{{ route('admin.settings.locations.update', $location) }}" class="space-y-3 p-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="location_id" value="{{ $location->id }}">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Name<input name="name" value="{{ old('location_id') == $location->id ? old('name') : $location->name }}" required maxlength="255" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                        <label class="text-[11px] font-medium text-slate-600 sm:col-span-2">Entity<input name="entity" value="{{ old('location_id') == $location->id ? old('entity') : $location->entity }}" required maxlength="100" class="mt-1 h-9 w-full border border-[#E5E7EB] px-2 text-[12px]"></label>
                        <label class="flex items-center gap-2 text-[11px]"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('location_id') == $location->id ? old('is_active', $location->is_active) : $location->is_active)> Active</label>
                        <label class="flex items-center gap-2 text-[11px]"><input type="hidden" name="navigation_enabled" value="0"><input type="checkbox" name="navigation_enabled" value="1" @checked(old('location_id') == $location->id ? old('navigation_enabled', $location->navigation_enabled) : $location->navigation_enabled)> Show in navigation</label>
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
            const failedLocationId = @json(old('location_id'));
            document.getElementById(failedLocationId ? `editLocationModal-${failedLocationId}` : 'createLocationModal')?.classList.replace('hidden', 'flex');
        </script>
    @endif
@endpush
