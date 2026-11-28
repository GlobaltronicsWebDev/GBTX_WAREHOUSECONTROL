@extends('layouts.operations-dashboard')

@section('title', 'Delivery Receipts')
@section('breadcrumb', 'Logistics / Delivery Receipts')

@section('content')
    <div class="space-y-4 px-3 py-4 sm:px-5">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E5E7EB] pb-3">
            <div>
                <h1 class="text-[20px] font-semibold text-slate-900">Delivery Receipts</h1>
                <p class="mt-1 text-[12px] text-slate-500">Draft, release and print client or internal receipts.</p>
            </div>
            <a href="{{ route('admin.delivery-receipts.create') }}" class="rounded-sm bg-[#2563EB] px-3 py-2 text-[12px] font-medium text-white hover:bg-blue-700">New Delivery Receipt</a>
        </div>
        <div class="overflow-x-auto border border-[#E5E7EB] bg-white">
            <table class="w-full min-w-150 text-left text-[12px]">
                <thead class="bg-[#F8F9FA] text-slate-500">
                    <tr><th class="px-3 py-2">DR Number</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">Date</th><th class="px-3 py-2">Client / Site</th><th class="px-3 py-2">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td class="px-3 py-3"><a class="font-medium text-[#2563EB] hover:underline" href="{{ route('admin.delivery-receipts.show', $receipt) }}">{{ $receipt->number }}</a></td>
                            <td class="px-3 py-3">{{ ucfirst($receipt->type) }}</td>
                            <td class="px-3 py-3">{{ $receipt->receipt_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-3">{{ $receipt->recipient }}</td>
                            <td class="px-3 py-3">{{ ucfirst($receipt->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">No delivery receipts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($receipts->hasPages())
                <div class="border-t border-[#E5E7EB] px-3 py-2">{{ $receipts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
