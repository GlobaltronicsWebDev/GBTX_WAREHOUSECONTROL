@extends('layouts.operations-dashboard')

@section('title', 'Inventory List')
@section('breadcrumb', 'Inventory / List')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <div class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-[20px] font-semibold tracking-tight text-slate-900">Inventory List</h1>
                <p class="mt-1 text-[12px] text-slate-500">Track received stock, availability, reservations, and history.</p>
            </div>
            <a href="{{ route('admin.inventory.create') }}" class="inline-flex h-9 items-center justify-center rounded-sm bg-[#2563EB] px-3 text-[12px] font-medium text-white hover:bg-blue-700">Add Item</a>
        </div>

        @if (session('status'))
            <div role="status" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <form method="GET" class="flex gap-2">
            <label for="inventory-search" class="sr-only">Search inventory</label>
            <input id="inventory-search" name="search" value="{{ request('search') }}" placeholder="Search tag, PO/SKU, model, or location" class="min-w-0 flex-1 rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
            <button type="submit" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-700 hover:bg-[#F8F9FA]">Search</button>
        </form>

        <div class="overflow-x-auto border border-[#E5E7EB] bg-white">
            <table class="min-w-[1500px] w-full text-left text-[11px]">
                <thead class="bg-[#F8F9FA] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Tag #</th>
                        <th class="px-3 py-2">Date Received</th>
                        <th class="px-3 py-2">PO / SKU No.</th>
                        <th class="px-3 py-2">Manufacturer</th>
                        <th class="px-3 py-2">Model / Pixel Pitch</th>
                        <th class="px-3 py-2">Item Description</th>
                        <th class="px-3 py-2">Location</th>
                        <th class="px-3 py-2">On-hand</th>
                        <th class="px-3 py-2">Available Qty</th>
                        <th class="px-3 py-2">Reservation</th>
                        <th class="px-3 py-2">History Qty</th>
                        <th class="px-3 py-2">History Project</th>
                        <th class="px-3 py-2">Original Qty</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-slate-700">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-3 py-2 font-medium">{{ $item->tag_number ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->date_received?->format('Y-m-d') ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->po_sku_number ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->manufacturer ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->model_pixel_pitch ?: '—' }}</td>
                            <td class="max-w-xs px-3 py-2">{{ $item->item_description }}</td>
                            <td class="px-3 py-2">{{ $item->warehouseLocation?->name ?: $item->location ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->total_on_hand }}</td>
                            <td class="px-3 py-2">{{ $item->available_qty }}</td>
                            <td class="px-3 py-2">{{ $item->reservation_qty }}</td>
                            <td class="px-3 py-2">{{ $item->history_qty }}</td>
                            <td class="max-w-xs px-3 py-2">{{ $item->history_project ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->original_qty }}</td>
                            <td class="px-3 py-2">{{ $item->status_particular ?: '—' }}</td>
                            <td class="max-w-xs px-3 py-2">{{ $item->remarks ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-3 py-10 text-center text-slate-400">No inventory items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $items->links() }}
    </div>
@endsection
