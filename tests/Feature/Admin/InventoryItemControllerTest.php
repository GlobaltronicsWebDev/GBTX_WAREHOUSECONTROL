<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\Role;
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

    public function test_item_can_be_edited_from_inventory_and_warehouse_lists(): void
    {
        $creator = User::factory()->create();
        $editor = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $destination = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create([
            'item_description' => 'Original panel',
            'date_received' => '2025-07-29',
            'warehouse_location_id' => $warehouse->id,
            'location' => $warehouse->name,
            'created_by' => $creator->id,
            'original_qty' => 42,
        ]);

        $this->actingAs($editor)->get(route('admin.inventory.index'))->assertSee(route('admin.inventory.edit', $item));
        $this->get(route('admin.warehouses.show', $warehouse))->assertSee(route('admin.inventory.edit', $item));
        $this->get(route('admin.inventory.edit', $item))
            ->assertSee('Edit Inventory Item')
            ->assertSee('value="2025-07-29"', false)
            ->assertSee('>Original panel</textarea>', false)
            ->assertSee('value="42"', false);

        $data = $this->itemData($destination->id);
        $data['item_description'] = 'Updated panel';
        $this->put(route('admin.inventory.update', $item), $data)
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('status', 'Inventory item updated successfully.');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'item_description' => 'Updated panel',
            'warehouse_location_id' => $destination->id,
            'location' => $destination->name,
            'created_by' => $creator->id,
        ]);
        $this->get(route('admin.warehouses.show', $warehouse))->assertDontSee('Updated panel');
        $this->get(route('admin.warehouses.show', $destination))->assertSee('Updated panel');
    }

    public function test_item_update_rejects_inactive_warehouse_and_invalid_fields(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $inactive = WarehouseLocation::factory()->create(['is_active' => false]);
        $item = InventoryItem::create(['item_description' => 'Original panel', 'warehouse_location_id' => $warehouse->id]);
        $data = $this->itemData($inactive->id);
        $data['item_description'] = '';
        $data['original_qty'] = -1;

        $this->actingAs($user)->from(route('admin.inventory.edit', $item))
            ->put(route('admin.inventory.update', $item), $data)
            ->assertRedirect(route('admin.inventory.edit', $item))
            ->assertSessionHasErrors(['warehouse_location_id', 'item_description', 'original_qty']);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'item_description' => 'Original panel',
            'warehouse_location_id' => $warehouse->id,
        ]);
    }

    public function test_existing_inactive_warehouse_can_be_retained_when_editing(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create(['is_active' => false, 'navigation_enabled' => false]);
        $item = InventoryItem::create(['item_description' => 'Original panel', 'warehouse_location_id' => $warehouse->id]);

        $this->actingAs($user)->get(route('admin.inventory.edit', $item))->assertSee($warehouse->name);
        $this->put(route('admin.inventory.update', $item), $this->itemData($warehouse->id))
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'warehouse_location_id' => $warehouse->id]);
    }

    public function test_guests_cannot_edit_inventory(): void
    {
        $item = InventoryItem::create(['item_description' => 'Original panel']);

        $this->get(route('admin.inventory.edit', $item))->assertRedirect(route('login'));
        $this->put(route('admin.inventory.update', $item), [])->assertRedirect(route('login'));
    }

    public function test_administrators_can_soft_delete_inventory_from_both_lists(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);

        foreach (['warehouse-admin', 'it-admin'] as $role) {
            $user = User::factory()->create();
            $user->roles()->attach(Role::where('slug', $role)->firstOrFail());
            $item = InventoryItem::create([
                'item_description' => 'Panel '.$role,
                'warehouse_location_id' => $warehouse->id,
            ]);

            $this->actingAs($user)->get(route('admin.inventory.index'))
                ->assertSee('action="'.route('admin.inventory.destroy', $item).'"', false)
                ->assertSee('aria-label="Delete Panel '.$role.'"', false);
            $this->get(route('admin.warehouses.show', $warehouse))
                ->assertSee('action="'.route('admin.inventory.destroy', $item).'"', false)
                ->assertSee('aria-label="Edit Panel '.$role.'"', false);
            $this->from(route('admin.warehouses.show', $warehouse))
                ->delete(route('admin.inventory.destroy', $item))
                ->assertRedirect(route('admin.warehouses.show', $warehouse))
                ->assertSessionHas('status', 'Inventory item deleted successfully.');

            $this->assertSoftDeleted($item);
            $this->get(route('admin.inventory.index'))->assertDontSee('Panel '.$role);
            $this->get(route('admin.warehouses.show', $warehouse))->assertDontSee('Panel '.$role);
            $this->get(route('admin.inventory.edit', $item))->assertNotFound();
            $this->assertTrue(InventoryItem::withTrashed()->findOrFail($item->id)->restore());
        }
    }

    public function test_non_administrators_and_guests_cannot_delete_inventory(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create([
            'item_description' => 'Restricted panel',
            'warehouse_location_id' => $warehouse->id,
        ]);

        $this->delete(route('admin.inventory.destroy', $item))->assertRedirect(route('login'));

        foreach (['warehouse-staff', 'technical'] as $role) {
            $user = User::factory()->create();
            $user->roles()->attach(Role::where('slug', $role)->firstOrFail());

            $this->actingAs($user)->get(route('admin.inventory.index'))
                ->assertDontSee('action="'.route('admin.inventory.destroy', $item).'"', false)
                ->assertSee('aria-label="Edit Restricted panel"', false);
            $this->get(route('admin.warehouses.show', $warehouse))
                ->assertDontSee('action="'.route('admin.inventory.destroy', $item).'"', false);
            $this->delete(route('admin.inventory.destroy', $item))->assertForbidden();
        }

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'deleted_at' => null]);
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
