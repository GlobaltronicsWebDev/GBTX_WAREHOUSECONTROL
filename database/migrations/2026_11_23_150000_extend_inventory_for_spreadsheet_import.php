<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->bigInteger('status_qty')->default(0)->change();
        });

        foreach (['A Juan', 'Clark Office'] as $name) {
            $exists = DB::table('warehouse_locations')->get(['name'])
                ->contains(fn ($warehouse) => strtolower(trim($warehouse->name)) === strtolower($name));
            if (! $exists) {
                DB::table('warehouse_locations')->insert([
                    'name' => $name,
                    'entity' => 'Warehouse',
                    'is_active' => true,
                    'navigation_enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('inventory_items')->where('status_qty', '<', 0)->orWhere('status_qty', '>', 4294967295)->exists()) {
            throw new RuntimeException('Cannot restore unsigned status quantities while values are outside the unsigned integer range.');
        }

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->unsignedInteger('status_qty')->default(0)->change();
        });

        // Warehouses may have been used or edited since this migration ran.
    }
};
