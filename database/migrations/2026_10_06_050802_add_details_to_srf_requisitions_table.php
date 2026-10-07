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
            $table->string('client')->nullable()->after('project_name');
            $table->string('po_number')->nullable()->after('client');
            $table->date('date_needed')->nullable()->after('po_number');
            $table->date('requisition_date')->nullable()->after('date_needed');
            $table->string('uom', 50)->default('PCS')->after('quantity');
            $table->text('remarks')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srf_requisitions', function (Blueprint $table) {
            $table->dropColumn(['client', 'po_number', 'date_needed', 'requisition_date', 'uom', 'remarks']);
        });
    }
};
