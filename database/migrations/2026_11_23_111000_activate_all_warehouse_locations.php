<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_locations')) {
            DB::table('warehouse_locations')->update([
                'is_active' => true,
                'navigation_enabled' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Existing warehouse activation states cannot be restored safely.
    }
};
