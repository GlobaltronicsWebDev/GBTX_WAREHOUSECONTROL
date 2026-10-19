<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // IT Admin
        $itAdmin = User::updateOrCreate(
            ['email' => 'mark.estoesta@globaltronics.net'],
            [
                'name' => 'Mark Estoesta',
                'password' => 'GlobaltronicsAdmin@2026',
            ]
        );
        $itAdminRole = Role::where('slug', 'it-admin')->first();
        if ($itAdminRole) {
            $itAdmin->roles()->syncWithoutDetaching([$itAdminRole->id]);
        }

        // Warehouse Admin
        $warehouseAdmin = User::updateOrCreate(
            ['email' => 'joshua.labios@globaltronics.net'],
            [
                'name' => 'Joshua Labios',
                'password' => 'GlobaltronicsAdmin@2026',
            ]
        );
        $warehouseAdminRole = Role::where('slug', 'warehouse-admin')->first();
        if ($warehouseAdminRole) {
            $warehouseAdmin->roles()->syncWithoutDetaching([$warehouseAdminRole->id]);
        }

        // Default Warehouse Staff User
        $staffUser = User::updateOrCreate(
            ['email' => 'staff@globaltronics.net'],
            [
                'name' => 'Warehouse Staff User',
                'password' => 'GlobaltronicsUser@2026',
            ]
        );
        $staffRole = Role::where('slug', 'warehouse-staff')->first();
        if ($staffRole) {
            $staffUser->roles()->syncWithoutDetaching([$staffRole->id]);
        }

        // Technical Specialist (Ariel Moro - Technical SRF Requisition & Hardware Handler)
        $techRole = Role::where('slug', 'technical')->first();
        $arielMoro = User::updateOrCreate(
            ['email' => 'ariel.moro@globaltronics.net'],
            [
                'name' => 'Ariel Moro',
                'password' => 'GlobaltronicsTech@2026',
            ]
        );
        if ($techRole) {
            $arielMoro->roles()->sync([$techRole->id]);
        }

        $salesRole = Role::where('slug', 'sales-executive')->first();
        $adsSalesRole = Role::where('slug', 'sales')->first();

        $salesStaffMembers = [
            [
                'name' => 'Sales Account Executive',
                'email' => 'sales@globaltronics.net',
                'password' => 'GlobaltronicsSales@2026',
            ],
            [
                'name' => 'Anne Liboon',
                'email' => 'anne.liboon@globaltronics.net',
                'password' => 'GlobaltronicsSales@2026',
            ],
            [
                'name' => 'Sheryl Quilla',
                'email' => 'sheryl.quilla@globaltronics.net',
                'password' => 'GlobaltronicsSales@2026',
            ],
        ];

        $roleIdsToSync = collect([$salesRole?->id, $adsSalesRole?->id])->filter()->values()->all();

        foreach ($salesStaffMembers as $staff) {
            $salesAccount = User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password' => $staff['password'],
                    'is_admin' => false,
                ]
            );

            if (!empty($roleIdsToSync)) {
                $salesAccount->roles()->syncWithoutDetaching($roleIdsToSync);
            }
        }

        // ADS Sales Order Workflow Accounts (Stage 1 through Stage 7)
        $stageUsers = [
            [
                'name' => 'Jovy',
                'email' => 'jovy@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role' => \App\Support\Workflow::ROLE_JOVY,
            ],
            [
                'name' => 'Fred',
                'email' => 'fred@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role' => \App\Support\Workflow::ROLE_FRED,
            ],
            [
                'name' => 'Owen',
                'email' => 'owen@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role' => \App\Support\Workflow::ROLE_OWEN,
            ],
            [
                'name' => 'Macy Guido Lee',
                'email' => 'macy@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role' => \App\Support\Workflow::ROLE_MACY,
            ],
            [
                'name' => 'Badet',
                'email' => 'badet@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role' => \App\Support\Workflow::ROLE_BADET,
            ],
            [
                'name' => 'King',
                'email' => 'king@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role' => \App\Support\Workflow::ROLE_KING,
            ],
            [
                'name' => 'Content Team',
                'email' => 'content@globaltronics.net',
                'password' => 'GlobaltronicsContent@2026',
                'role' => \App\Support\Workflow::ROLE_CONTENT,
            ],
            [
                'name' => 'IT Operations',
                'email' => 'it@globaltronics.net',
                'password' => 'GlobaltronicsIt@2026',
                'role' => \App\Support\Workflow::ROLE_IT,
            ],
            [
                'name' => 'Accounting Team',
                'email' => 'accounting@globaltronics.net',
                'password' => 'GlobaltronicsAcct@2026',
                'role' => \App\Support\Workflow::ROLE_ACCOUNTING,
            ],
        ];

        foreach ($stageUsers as $stg) {
            $user = User::updateOrCreate(
                ['email' => $stg['email']],
                [
                    'name' => $stg['name'],
                    'password' => $stg['password'],
                    'is_admin' => false,
                ]
            );

            $role = Role::where('slug', $stg['role'])->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        // Seed Inventory items (EOL Philips Units)
        $this->call(InventoryItemSeeder::class);
    }
}
