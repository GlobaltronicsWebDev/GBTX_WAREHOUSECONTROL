<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_item_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('project')->nullable();
            $table->timestamps();
        });

        DB::table('inventory_items')
            ->where(function ($query): void {
                $query->where('history_qty', '!=', 0)
                    ->orWhere(function ($query): void {
                        $query->whereNotNull('history_project')->where('history_project', '!=', '');
                    });
            })
            ->orderBy('id')
            ->chunkById(200, function ($items): void {
                $histories = $items->map(fn ($item) => [
                    'inventory_item_id' => $item->id,
                    'quantity' => $item->history_qty,
                    'project' => $item->history_project,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ])->all();

                DB::table('inventory_item_histories')->insert($histories);
            });
    }

    public function down(): void
    {
        if (DB::table('inventory_item_histories')->exists()) {
            throw new RuntimeException('Cannot remove inventory history while entries exist.');
        }

        Schema::dropIfExists('inventory_item_histories');
    }
};
