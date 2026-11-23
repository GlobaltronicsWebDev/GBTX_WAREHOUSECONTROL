@extends('layouts.operations-dashboard')

@section('title', $location->name.' Warehouse')
@section('breadcrumb', $location->name)

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <section class="flex flex-col gap-3 border-b border-[#E5E7EB] pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-blue-50 text-blue-600" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6M9 10h.01M15 10h.01"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-[18px] font-semibold leading-6 text-slate-900">{{ $location->name }} Warehouse</h1>
                    <p class="mt-1 text-[11px] text-slate-500">Warehouse overview and stock list</p>
                </div>
            </div>
            <span class="w-fit rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-medium text-emerald-700">Active location</span>
        </section>

        <section class="grid gap-3 sm:grid-cols-2" aria-label="Warehouse details">
            <article class="border border-[#E5E7EB] bg-white px-4 py-3">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Location</p>
                <p class="mt-1 text-[13px] font-medium text-slate-900">{{ $location->name }}</p>
            </article>
            <article class="border border-[#E5E7EB] bg-white px-4 py-3">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Entity</p>
                <p class="mt-1 text-[13px] font-medium text-slate-900">{{ $location->entity }}</p>
            </article>
        </section>

        <section class="border border-[#E5E7EB] bg-white" aria-labelledby="warehouseStockHeading">
            <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3">
                <div>
                    <h2 id="warehouseStockHeading" class="text-[13px] font-semibold text-slate-900">Stock overview</h2>
                    <p class="mt-1 text-[10px] text-slate-500">Stock records for {{ $location->name }}</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-medium text-emerald-700">{{ $items->count() }} item(s)</span>
            </div>

            @if ($items->isEmpty())
                <div class="flex min-h-44 flex-col items-center justify-center gap-2 px-4 py-8 text-center">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linejoin="round" d="m3 7 9-4 9 4-9 4-9-4Z"/><path stroke-linejoin="round" d="M3 7v10l9 4 9-4V7M12 11v10"/>
                    </svg>
                </span>
                <h3 class="text-[12px] font-medium text-slate-800">Stock tracking is not configured</h3>
                <p class="max-w-md text-[10px] leading-5 text-slate-500">This location is ready, but stock records are not available yet. No inventory totals or item rows are being shown.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-[900px] w-full text-left text-[11px]">
                        <thead class="border-b border-[#E5E7EB] bg-[#F8F9FA] text-slate-500">
                            <tr><th class="px-4 py-2">Tag #</th><th class="px-4 py-2">Manufacturer</th><th class="px-4 py-2">Model</th><th class="px-4 py-2">Description</th>@if ($location->inventorySubLocations())<th class="px-4 py-2">Sub-location</th>@endif<th class="px-4 py-2">Available Qty</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Actions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @foreach ($items as $item)
                                <tr>
                                    <td class="px-4 py-2 font-medium">{{ $item->tag_number ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $item->manufacturer ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $item->model_pixel_pitch ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $item->item_description }}</td>
                                    @if ($location->inventorySubLocations())
                                        <td class="px-4 py-2">{{ $item->sub_location ?: '—' }}</td>
                                    @endif
                                    <td class="px-4 py-2">{{ $item->available_qty }}</td>
                                    <td class="px-4 py-2">{{ $item->status_particular ?: '—' }}</td>
                                    <td class="px-4 py-2">@include('admin.inventory._actions', ['item' => $item])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
