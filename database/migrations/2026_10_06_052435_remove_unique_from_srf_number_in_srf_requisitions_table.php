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
        Schema::table('srf_requisitions', function (Blueprint $table) {
            $table->dropUnique(['srf_number']);
            $table->index('srf_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srf_requisitions', function (Blueprint $table) {
            $table->dropIndex(['srf_number']);
            $table->unique('srf_number');
        });
    }
};
