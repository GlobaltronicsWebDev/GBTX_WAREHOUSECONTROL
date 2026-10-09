<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'original_quantity')) {
                $table->integer('original_quantity')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('inventory_items', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }
            if (!Schema::hasColumn('inventory_items', 'reservation_qty')) {
                $table->integer('reservation_qty')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('inventory_items', 'reservation_project')) {
                $table->string('reservation_project')->nullable()->after('reservation_qty');
            }
            if (!Schema::hasColumn('inventory_items', 'reservation_remarks')) {
                $table->text('reservation_remarks')->nullable()->after('reservation_project');
            }
            if (!Schema::hasColumn('inventory_items', 'history_qty')) {
                $table->integer('history_qty')->nullable()->after('reservation_remarks');
            }
            if (!Schema::hasColumn('inventory_items', 'history_project')) {
                $table->string('history_project')->nullable()->after('history_qty');
            }
            if (!Schema::hasColumn('inventory_items', 'status_qty')) {
                $table->integer('status_qty')->nullable()->after('history_project');
            }
            if (!Schema::hasColumn('inventory_items', 'status_particular')) {
                $table->text('status_particular')->nullable()->after('status_qty');
            }
            if (!Schema::hasColumn('inventory_items', 'movement_history')) {
                $table->json('movement_history')->nullable()->after('status_particular');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'original_quantity',
                'remarks',
                'reservation_qty',
                'reservation_project',
                'reservation_remarks',
                'history_qty',
                'history_project',
                'status_qty',
                'status_particular',
                'movement_history',
            ]);
        });
    }
};
