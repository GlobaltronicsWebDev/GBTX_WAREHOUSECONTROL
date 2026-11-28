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
        Schema::create('delivery_receipt_sequences', function (Blueprint $table): void {
            $table->string('type', 10)->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });

        DB::table('delivery_receipt_sequences')->insert([
            ['type' => 'client', 'last_number' => 0],
            ['type' => 'internal', 'last_number' => 0],
        ]);

        Schema::create('delivery_receipts', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->string('type', 10);
            $table->string('status', 10)->default('draft');
            $table->date('receipt_date');
            $table->string('recipient');
            $table->string('address')->nullable();
            $table->string('tin')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('terms')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->string('sales_order_number')->nullable();
            $table->string('request_number')->nullable();
            $table->string('received_by')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('stock_transaction_id')->nullable()->unique()->constrained('stock_transactions')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('delivery_receipt_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_sku');
            $table->string('product_name');
            $table->string('model_serial')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('unit_of_measurement', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('delivery_receipts')->exists()) {
            throw new RuntimeException('Cannot remove delivery receipt history while receipts exist.');
        }

        Schema::dropIfExists('delivery_receipt_lines');
        Schema::dropIfExists('delivery_receipts');
        Schema::dropIfExists('delivery_receipt_sequences');
    }
};
