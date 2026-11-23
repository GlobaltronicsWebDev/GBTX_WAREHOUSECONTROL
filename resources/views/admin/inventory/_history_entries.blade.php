<div class="space-y-1">
    @forelse ($item->histories as $history)
        @if ($loop->iteration === 2)
            <details>
                <summary class="cursor-pointer text-blue-600 hover:text-blue-700">Additional histories ({{ $loop->count - 1 }})</summary>
                <div class="space-y-1 pt-1">
        @endif
        <div class="flex items-start gap-3">
            <span class="w-12 shrink-0 tabular-nums">{{ $history->quantity ?? '—' }}</span>
            <span class="whitespace-pre-line">{{ $history->project ?: '—' }}</span>
        </div>
        @if ($loop->last && ! $loop->first)
                </div>
            </details>
        @endif
    @empty
        <span class="text-slate-400">—</span>
    @endforelse
</div>
