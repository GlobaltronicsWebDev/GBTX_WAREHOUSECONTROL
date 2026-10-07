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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('EOL PHILIPS UNITS')->index();
            $table->string('manufacturer')->index();
            $table->date('check_in_date')->index();
            $table->string('model')->index();
            $table->text('item_description');
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable();
            $table->string('status')->default('in_stock')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
