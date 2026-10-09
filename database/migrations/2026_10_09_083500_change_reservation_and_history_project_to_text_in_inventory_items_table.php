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
        if (Schema::hasTable('inventory_items')) {
            try {
                DB::statement('ALTER TABLE inventory_items MODIFY COLUMN reservation_project TEXT NULL');
                DB::statement('ALTER TABLE inventory_items MODIFY COLUMN history_project TEXT NULL');
            } catch (\Throwable $e) {
                // Fallback for non-MySQL or already altered
                Schema::table('inventory_items', function (Blueprint $table) {
                    if (Schema::hasColumn('inventory_items', 'reservation_project')) {
                        $table->text('reservation_project')->nullable()->change();
                    }
                    if (Schema::hasColumn('inventory_items', 'history_project')) {
                        $table->text('history_project')->nullable()->change();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_items')) {
            try {
                DB::statement('ALTER TABLE inventory_items MODIFY COLUMN reservation_project VARCHAR(255) NULL');
                DB::statement('ALTER TABLE inventory_items MODIFY COLUMN history_project VARCHAR(255) NULL');
            } catch (\Throwable $e) {
                // Ignore rollback failure
            }
        }
    }
};
