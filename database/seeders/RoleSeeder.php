<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'IT Administrator',
                'slug' => 'it-admin',
                'description' => 'Full administrative access to user accounts, roles, security policies, and system configs.',
                'badge_color' => 'purple',
                'is_system' => true,
            ],
            [
                'name' => 'Warehouse Admin',
                'slug' => 'warehouse-admin',
                'description' => 'Oversees warehouse operations and stock requisition approvals.',
                'badge_color' => 'amber',
                'is_system' => true,
            ],
            [
                'name' => 'Warehouse Staff',
                'slug' => 'warehouse-staff',
                'description' => 'Floor operational personnel managing item picking, pallet loading, and barcode registration.',
                'badge_color' => 'emerald',
                'is_system' => false,
            ],
            [
                'name' => 'Technical',
                'slug' => 'technical',
                'description' => 'Technical personnel handling Stock Requisition Forms (SRF), site installation hardware, and diagnostics.',
                'badge_color' => 'cyan',
                'is_system' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
