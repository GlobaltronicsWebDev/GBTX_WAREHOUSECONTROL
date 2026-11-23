<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->foreignId('warehouse_location_id')
                ->nullable()
                ->after('location')
                ->constrained('warehouse_locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropForeign(['warehouse_location_id']);
            $table->dropColumn('warehouse_location_id');
        });
    }
};
