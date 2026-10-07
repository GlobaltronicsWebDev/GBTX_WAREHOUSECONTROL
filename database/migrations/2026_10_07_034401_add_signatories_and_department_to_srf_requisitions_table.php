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
            $table->string('department')->nullable()->default('TECHNICAL')->after('status');
            $table->string('noted_by')->nullable()->after('prepared_by');
            $table->string('pre_approved_by')->nullable()->after('noted_by');
            $table->string('approved_by')->nullable()->default('Macy Guido Lee')->after('pre_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srf_requisitions', function (Blueprint $table) {
            $table->dropColumn(['department', 'noted_by', 'pre_approved_by', 'approved_by']);
        });
    }
};
