<div class="space-y-1">
    @forelse ($item->histories as $history)
        <div class="flex items-start gap-3">
            <span class="w-12 shrink-0 tabular-nums">{{ $history->quantity ?? '—' }}</span>
            <span class="whitespace-pre-line">{{ $history->project ?: '—' }}</span>
        </div>
    @empty
        <span class="text-slate-400">—</span>
    @endforelse
</div>
