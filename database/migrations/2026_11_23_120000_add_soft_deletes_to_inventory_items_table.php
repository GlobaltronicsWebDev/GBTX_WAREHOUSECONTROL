<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (DB::table('inventory_items')->whereNotNull('deleted_at')->exists()) {
            throw new RuntimeException('Cannot remove soft deletes while deleted inventory items exist.');
        }

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
