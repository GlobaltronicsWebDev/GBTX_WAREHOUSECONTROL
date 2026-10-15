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


        // Seed Inventory items (EOL Philips Units)
        $this->call(InventoryItemSeeder::class);
    }
}
