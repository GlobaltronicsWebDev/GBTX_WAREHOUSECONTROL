<?php

use App\Support\Workflow;
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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('aso_number')->unique()->index();
            $table->string('sjo_number')->nullable()->unique()->index();
            $table->date('date_filed')->index();
            $table->string('bds_name')->index();
            $table->string('bds_email');
            $table->string('client_name')->index();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->string('current_step')->default(Workflow::STEP_ASO_APPROVAL_1)->index();
            $table->string('status')->default(Workflow::STATUS_IN_PROGRESS)->index();
            $table->json('data')->nullable(); // Stores all ASO fields
            $table->json('sjo')->nullable();  // Stores SJO fields
            $table->json('it')->nullable();   // Stores IT monitoring/upload details
            $table->json('checks')->nullable(); // Stores Accounting checklist
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Filer
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
