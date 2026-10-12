<?php

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $exists = InventoryItem::where(function ($q) {
                $q->where('category', 'LED Service Units')
                    ->orWhere('category', 'LED Services Units');
            })->where('manufacturer', 'LEDTOP')->exists();

            if (! $exists) {
                $creatorId = User::value('id');

                InventoryItem::create([
                    'category' => 'LED Service Units',
                    'tag_number' => '7',
                    'location' => 'GLOBALTRONICS',
                    'manufacturer' => 'LEDTOP',
                    'check_in_date' => '2026-07-08',
                    'model' => 'LEDTOP-P3.91',
                    'po_number' => 'PO-2026-0722',
                    'item_description' => '500x500mm LEDTOP Outdoor & Indoor Rental Cabinet, High Refresh Rate Event Panel',
                    'quantity' => 50,
                    'acu_quantity' => 50,
                    'sqm' => 12.50,
                    'forecasted_quantity' => 50,
                    'original_quantity' => 50,
                    'status' => 'in_stock',
                    'created_by' => $creatorId,
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore if tables are not ready during initial setup
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            InventoryItem::where(function ($q) {
                $q->where('category', 'LED Service Units')
                    ->orWhere('category', 'LED Services Units');
            })->where('manufacturer', 'LEDTOP')->delete();
        } catch (\Throwable $e) {
        }
    }
};
