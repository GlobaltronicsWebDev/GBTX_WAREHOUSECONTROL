<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:sync-po', function () {
    $ledJsonPath = storage_path('led_records.json');
    if (!file_exists($ledJsonPath)) {
        $this->error("led_records.json not found in storage!");
        return;
    }

    $records = json_decode(file_get_contents($ledJsonPath), true) ?: [];
    $updated = 0;

    foreach ($records as $rec) {
        $po = !empty($rec['po_number']) ? trim($rec['po_number']) : null;
        if (!empty($rec['tag_number'])) {
            $count = \App\Models\InventoryItem::where('category', 'CENTRALIZED LED INVENTORY')
                ->where('tag_number', $rec['tag_number'])
                ->update(['po_number' => $po]);
            if ($count > 0) {
                $updated++;
            }
        }
    }

    $this->info("Successfully synced PO # for {$updated} Centralized LED inventory items!");
})->purpose('Sync PO numbers from led_records.json into inventory_items');

