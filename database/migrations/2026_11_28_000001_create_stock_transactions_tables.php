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
            $table->string('unit_of_measurement', 50)->default('PCS')->after('item_description');
        });

        Schema::create('stock_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number')->nullable()->unique();
            $table->string('type', 3);
            $table->date('transaction_date');
            $table->string('purchase_order_number')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('request_number')->nullable();
            $table->string('department_customer')->nullable();
            $table->string('released_by')->nullable();
            $table->string('received_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'transaction_date', 'id']);
        });

        Schema::create('stock_transaction_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stock_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_sku')->nullable();
            $table->string('product_name');
            $table->string('warehouse_location_name')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('unit_of_measurement', 50);
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->timestamps();

            $table->index(['stock_transaction_id', 'id']);
        });
    }

    public function down(): void
    {
        if (DB::table('stock_transactions')->exists() || DB::table('stock_transaction_lines')->exists()) {
            throw new \RuntimeException('Cannot remove stock transaction history while entries exist.');
        }

        if (DB::table('inventory_items')->where('unit_of_measurement', '!=', 'PCS')->exists()) {
            throw new \RuntimeException('Cannot remove custom inventory units while inventory items use them.');
        }

        Schema::dropIfExists('stock_transaction_lines');
        Schema::dropIfExists('stock_transactions');

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropColumn('unit_of_measurement');
        });
    }
};
