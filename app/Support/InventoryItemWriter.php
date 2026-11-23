<?php

namespace App\Support;

use App\Models\InventoryItem;

class InventoryItemWriter
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function save(InventoryItem $item, array $validated): void
    {
        $historiesSubmitted = ($validated['history_submitted'] ?? false) || array_key_exists('histories', $validated);
        $histories = array_values(array_filter($validated['histories'] ?? [], fn (array $history): bool => ($history['quantity'] ?? null) !== null || ($history['project'] ?? null) !== null));
        unset($validated['histories'], $validated['history_submitted']);

        if ($historiesSubmitted) {
            $validated['history_qty'] = $histories[0]['quantity'] ?? 0;
            $validated['history_project'] = $histories[0]['project'] ?? null;
        }

        $item->fill($validated)->saveOrFail();

        if ($historiesSubmitted) {
            $item->histories()->delete();
            $item->histories()->createMany($histories);
        }
    }
}
