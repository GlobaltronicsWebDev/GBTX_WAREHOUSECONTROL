<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\InventoryItem;
use App\Models\User;
use App\Models\WarehouseLocation;
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
        $warehouseLocation = WarehouseLocation::factory()->create([
            'name' => 'Main Warehouse',
            'is_active' => true,
            'navigation_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.inventory.store'), $this->itemData($warehouseLocation->id));

        $response
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('status', 'Inventory item added successfully.');

        $this->assertDatabaseHas('inventory_items', [
            'tag_number' => 'TAG-001',
            'item_description' => 'LED panel',
            'total_on_hand' => 12,
            'created_by' => $user->id,
            'warehouse_location_id' => $warehouseLocation->id,
        ]);
    }

    public function test_authenticated_users_can_add_an_inventory_item(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->first());
        $warehouseLocation = WarehouseLocation::factory()->create([
            'is_active' => true,
            'navigation_enabled' => true,
        ]);

        $this->actingAs($user)
            ->post(route('admin.inventory.store'), $this->itemData($warehouseLocation->id))
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseHas('inventory_items', [
            'created_by' => $user->id,
            'warehouse_location_id' => $warehouseLocation->id,
        ]);
    }

    public function test_inventory_list_displays_original_quantity_after_history(): void
    {
        $user = User::factory()->create();
        InventoryItem::create([
            'item_description' => 'LED panel',
            'history_qty' => 17,
            'history_project' => 'Project Alpha',
            'original_qty' => 42,
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventory.index'));

        $response->assertSeeInOrder(['History Qty', 'History Project', 'Original Qty', 'Status']);
        $response->assertSeeInOrder(['>17</td>', '>Project Alpha</td>', '>42</td>'], false);
    }

    /**
     * @return array<string, int|string>
     */
    private function itemData(?int $warehouseLocationId = null): array
    {
        return [
            'tag_number' => 'TAG-001',
            'date_received' => '2026-11-23',
            'po_sku_number' => 'PO-001',
            'manufacturer' => 'Globaltronics',
            'model_pixel_pitch' => 'P2.5',
            'item_description' => 'LED panel',
            'location' => 'Main Warehouse',
            'warehouse_location_id' => $warehouseLocationId,
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
