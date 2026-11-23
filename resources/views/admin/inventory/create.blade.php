@extends('layouts.operations-dashboard')

@section('title', $item ? 'Edit Inventory Item' : 'Add Inventory Item')
@section('breadcrumb', $item ? 'Inventory / Edit Item' : 'Inventory / Add Item')

@section('content')
    <div class="mx-auto max-w-5xl space-y-4 px-3 py-4 sm:px-5">
        <div>
            <h1 class="text-[20px] font-semibold tracking-tight text-slate-900">{{ $item ? 'Edit Inventory Item' : 'Add Inventory Item' }}</h1>
            <p class="mt-1 text-[12px] text-slate-500">Enter the inventory details shown in the stock register.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.inventory.update', $item) : route('admin.inventory.store') }}" class="space-y-5 border border-[#E5E7EB] bg-white p-4 sm:p-5">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'tag_number' => 'Tag #',
                    'date_received' => 'Date Received',
                    'po_sku_number' => 'PO / SKU No.',
                    'manufacturer' => 'Manufacturer',
                    'model_pixel_pitch' => 'Model / Pixel Pitch',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-[11px] font-medium text-slate-700">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $field === 'date_received' ? $item?->date_received?->format('Y-m-d') : $item?->{$field}) }}" type="{{ $field === 'date_received' ? 'date' : 'text' }}" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                @endforeach
                <div>
                    <label for="warehouse_location_id" class="mb-1 block text-[11px] font-medium text-slate-700">Warehouse Location <span class="text-red-500">*</span></label>
                    <select id="warehouse_location_id" name="warehouse_location_id" required class="w-full rounded-sm border border-[#E5E7EB] bg-white px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                        <option value="">Select warehouse</option>
                        @foreach ($warehouseLocations as $warehouseLocation)
                            <option value="{{ $warehouseLocation->id }}" @selected(old('warehouse_location_id', $item?->warehouse_location_id) == $warehouseLocation->id)>{{ $warehouseLocation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="item_description" class="mb-1 block text-[11px] font-medium text-slate-700">Item Description <span class="text-red-500">*</span></label>
                <textarea id="item_description" name="item_description" required rows="3" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">{{ old('item_description', $item?->item_description) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    'on_hand' => 'On-hand',
                    'total_on_hand' => 'Total On-hand',
                    'available_qty' => 'Available Qty',
                    'reservation_qty' => 'Reservation Qty',
                    'history_qty' => 'History Qty',
                    'original_qty' => 'Original Qty',
                    'status_qty' => 'Status Qty',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-[11px] font-medium text-slate-700">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item?->{$field} ?? 0) }}" type="number" min="0" required class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                @endforeach
                @foreach ([
                    'per_panel_sqm' => 'Per Panel SQM',
                    'total_available_sqm' => 'Total Available SQM',
                    'available_sqm' => 'Available SQM',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-[11px] font-medium text-slate-700">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item?->{$field} ?? 0) }}" type="number" min="0" step="0.01" required class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    'reservation_project_details' => 'Reservation Project Details',
                    'history_project' => 'History Project',
                    'remarks' => 'Remarks',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-[11px] font-medium text-slate-700">{{ $label }}</label>
                        <textarea id="{{ $field }}" name="{{ $field }}" rows="3" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">{{ old($field, $item?->{$field}) }}</textarea>
                    </div>
                @endforeach
                <div>
                    <label for="status_particular" class="mb-1 block text-[11px] font-medium text-slate-700">Status Particular</label>
                    <input id="status_particular" name="status_particular" value="{{ old('status_particular', $item?->status_particular) }}" class="w-full rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] outline-none focus:border-[#2563EB]">
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-[#E5E7EB] pt-4">
                <a href="{{ route('admin.inventory.index') }}" class="rounded-sm border border-[#E5E7EB] px-3 py-2 text-[12px] text-slate-600 hover:bg-[#F8F9FA]">Cancel</a>
                <button type="submit" class="rounded-sm bg-[#2563EB] px-3 py-2 text-[12px] font-medium text-white hover:bg-blue-700">{{ $item ? 'Save Changes' : 'Save Item' }}</button>
            </div>
        </form>
    </div>
@endsection
