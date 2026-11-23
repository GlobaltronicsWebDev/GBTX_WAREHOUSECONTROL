<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_warehouse_admin_can_add_an_inventory_item(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->first());

        $response = $this->actingAs($user)->post(route('admin.inventory.store'), $this->itemData());

        $response
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('status', 'Inventory item added successfully.');

        $this->assertDatabaseHas('inventory_items', [
            'tag_number' => 'TAG-001',
            'item_description' => 'LED panel',
            'total_on_hand' => 12,
            'created_by' => $user->id,
        ]);
    }

    public function test_users_without_warehouse_operations_access_cannot_add_an_inventory_item(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->first());

        $this->actingAs($user)
            ->post(route('admin.inventory.store'), $this->itemData())
            ->assertForbidden();

        $this->assertDatabaseCount('inventory_items', 0);
    }

    /**
     * @return array<string, int|string>
     */
    private function itemData(): array
    {
        return [
            'tag_number' => 'TAG-001',
            'date_received' => '2026-11-23',
            'po_sku_number' => 'PO-001',
            'manufacturer' => 'Globaltronics',
            'model_pixel_pitch' => 'P2.5',
            'item_description' => 'LED panel',
            'location' => 'Main Warehouse',
            'on_hand' => 12,
            'total_on_hand' => 12,
            'per_panel_sqm' => 0.25,
            'total_available_sqm' => 3,
            'available_qty' => 12,
            'available_sqm' => 3,
            'reservation_qty' => 0,
            'reservation_project_details' => '',
            'history_qty' => 0,
            'history_project' => '',
            'original_qty' => 12,
            'status_qty' => 12,
            'status_particular' => 'Available',
            'remarks' => '',
        ];
    }
}
