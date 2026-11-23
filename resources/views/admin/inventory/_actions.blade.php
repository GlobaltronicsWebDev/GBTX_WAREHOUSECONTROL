<div class="flex items-center gap-2">
    <a href="{{ route('admin.inventory.edit', $item) }}" aria-label="Edit {{ $item->item_description }}" title="Edit" class="inline-flex p-1 text-slate-500 hover:text-[#2563EB]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z"/>
        </svg>
    </a>
    @if (Auth::user()->canDeleteInventoryItems())
        <form method="POST" action="{{ route('admin.inventory.destroy', $item) }}" onsubmit="return confirm('Delete this inventory item?')">
            @csrf
            @method('DELETE')
            <button type="submit" aria-label="Delete {{ $item->item_description }}" title="Delete" class="inline-flex p-1 text-red-600 hover:text-red-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                </svg>
            </button>
        </form>
    @endif
</div>
