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

        @if (Auth::user()->canDeleteInventoryItems() && $items->isNotEmpty())
            <form id="delete-selected-inventory" method="POST" action="{{ route('admin.inventory.destroy-selected', request()->only(['search', 'page'])) }}" class="flex flex-wrap items-center gap-3" onsubmit="return confirm('Soft-delete the selected items on this page? They cannot be restored from the application.')">
                @csrf
                @method('DELETE')
                <button id="delete-inventory-selected-button" type="submit" class="inline-flex items-center gap-1 rounded-sm border border-[#E5E7EB] px-2 py-1 text-[11px] text-red-600 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                    </svg>
                    Delete selected
                </button>
                <p class="text-[11px] text-slate-500">Selection is limited to this page. Deleted items cannot be restored from the application.</p>
            </form>
        @endif

        <div class="overflow-x-auto border border-[#E5E7EB] bg-white">
            <table class="min-w-[1500px] w-full text-left text-[11px]">
                <thead class="bg-[#F8F9FA] uppercase tracking-wide text-slate-500">
                    <tr>
                        @if (Auth::user()->canDeleteInventoryItems())
                            <th class="px-3 py-2">
                                <input id="select-inventory-page" type="checkbox" aria-label="Select all items on this page" hidden>
                            </th>
                        @endif
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
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-slate-700">
                    @forelse ($items as $item)
                        <tr>
                            @if (Auth::user()->canDeleteInventoryItems())
                                <td class="px-3 py-2">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" form="delete-selected-inventory" aria-label="Select {{ $item->item_description }}" data-inventory-item>
                                </td>
                            @endif
                            <td class="px-3 py-2 font-medium">{{ $item->tag_number ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->date_received?->format('Y-m-d') ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->po_sku_number ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->manufacturer ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $item->model_pixel_pitch ?: '—' }}</td>
                            <td class="max-w-xs px-3 py-2">{{ $item->item_description }}</td>
                            <td class="px-3 py-2">
                                {{ $item->warehouseLocation?->name ?: $item->location ?: '—' }}
                                @if ($item->sub_location)
                                    <span class="block text-slate-500">{{ $item->sub_location }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $item->total_on_hand }}</td>
                            <td class="px-3 py-2">{{ $item->available_qty }}</td>
                            <td class="px-3 py-2">{{ $item->reservation_qty }}</td>
                            <td colspan="2" class="max-w-xs px-3 py-2">@include('admin.inventory._history_entries', ['item' => $item])</td>
                            <td class="px-3 py-2">{{ $item->original_qty }}</td>
                            <td class="px-3 py-2">{{ $item->status_particular ?: '—' }}</td>
                            <td class="max-w-xs px-3 py-2">{{ $item->remarks ?: '—' }}</td>
                            <td class="px-3 py-2">@include('admin.inventory._actions', ['item' => $item])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->canDeleteInventoryItems() ? 17 : 16 }}" class="px-3 py-10 text-center text-slate-400">No inventory items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $items->links() }}
    </div>
@endsection

@push('scripts')
    <script>
        const selectInventoryPage = document.getElementById('select-inventory-page');
        const deleteInventoryButton = document.getElementById('delete-inventory-selected-button');
        if (selectInventoryPage && deleteInventoryButton) {
            const inventoryItems = Array.from(document.querySelectorAll('[data-inventory-item]'));
            const updateInventorySelection = () => {
                const selectedCount = inventoryItems.filter((checkbox) => checkbox.checked).length;
                selectInventoryPage.checked = selectedCount === inventoryItems.length;
                selectInventoryPage.indeterminate = selectedCount > 0 && selectedCount < inventoryItems.length;
                deleteInventoryButton.disabled = selectedCount === 0;
            };
            selectInventoryPage.hidden = false;
            selectInventoryPage.addEventListener('change', () => {
                inventoryItems.forEach((item) => { item.checked = selectInventoryPage.checked; });
                updateInventorySelection();
            });
            inventoryItems.forEach((item) => item.addEventListener('change', updateInventorySelection));
            updateInventorySelection();
        }
    </script>
@endpush
