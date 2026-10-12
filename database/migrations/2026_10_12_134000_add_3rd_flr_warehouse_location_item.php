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
            $exists = InventoryItem::where('location', '3RD FLR WAREHOUSE')
                ->orWhere('location', '3rd Flr Wrehouse')
                ->exists();

            if (! $exists) {
                $creatorId = User::value('id');

                InventoryItem::create([
                    'category' => 'LED Service Units',
                    'tag_number' => '8',
                    'location' => '3RD FLR WAREHOUSE',
                    'manufacturer' => 'LEDTOP',
                    'check_in_date' => '2026-07-08',
                    'model' => 'LEDTOP-P3.91',
                    'po_number' => 'PO-2026-0723',
                    'item_description' => '500x500mm LEDTOP Rental Display Cabinet (3rd Flr Warehouse Storage)',
                    'quantity' => 20,
                    'acu_quantity' => 20,
                    'sqm' => 5.00,
                    'forecasted_quantity' => 20,
                    'original_quantity' => 20,
                    'status' => 'in_stock',
                    'created_by' => $creatorId,
                ]);
            }
        } catch (\Throwable $e) {
            // Safe guard against setup errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            InventoryItem::where('location', '3RD FLR WAREHOUSE')
                ->orWhere('location', '3rd Flr Wrehouse')
                ->delete();
        } catch (\Throwable $e) {
        }
    }
};
