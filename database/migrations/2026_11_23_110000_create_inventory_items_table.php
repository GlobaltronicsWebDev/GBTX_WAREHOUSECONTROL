<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->string('tag_number')->nullable()->index();
            $table->date('date_received')->nullable()->index();
            $table->string('po_sku_number')->nullable()->index();
            $table->string('manufacturer')->nullable()->index();
            $table->string('model_pixel_pitch')->nullable()->index();
            $table->text('item_description');
            $table->string('location')->nullable()->index();
            $table->unsignedInteger('on_hand')->default(0);
            $table->unsignedInteger('total_on_hand')->default(0);
            $table->decimal('per_panel_sqm', 10, 2)->default(0);
            $table->decimal('total_available_sqm', 12, 2)->default(0);
            $table->unsignedInteger('available_qty')->default(0);
            $table->decimal('available_sqm', 12, 2)->default(0);
            $table->unsignedInteger('reservation_qty')->default(0);
            $table->text('reservation_project_details')->nullable();
            $table->unsignedInteger('history_qty')->default(0);
            $table->text('history_project')->nullable();
            $table->unsignedInteger('original_qty')->default(0);
            $table->unsignedInteger('status_qty')->default(0);
            $table->string('status_particular')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
