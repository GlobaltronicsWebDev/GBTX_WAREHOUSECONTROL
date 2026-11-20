<?php

namespace Database\Seeders;

use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;

class WarehouseLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Globaltronics', 'Ohaap', 'Marikina', 'Carmona'] as $name) {
            WarehouseLocation::firstOrCreate(
                ['name' => $name, 'entity' => 'Warehouse'],
                ['is_active' => true, 'navigation_enabled' => true],
            );
        }
    }
}
