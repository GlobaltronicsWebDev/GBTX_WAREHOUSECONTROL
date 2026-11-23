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
            $table->string('sub_location')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('inventory_items')->whereNotNull('sub_location')->exists()) {
            throw new RuntimeException('Cannot remove inventory sub-locations while they contain data.');
        }

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropColumn('sub_location');
        });
    }
};
