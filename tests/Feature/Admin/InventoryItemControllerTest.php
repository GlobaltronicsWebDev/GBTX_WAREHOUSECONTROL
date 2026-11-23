<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RoleSeeder;
use DOMDocument;
use DOMXPath;
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
        $item = InventoryItem::create([
            'item_description' => 'LED panel',
            'history_qty' => 17,
            'history_project' => 'Project Alpha',
            'original_qty' => 42,
        ]);
        $item->histories()->create(['quantity' => 17, 'project' => 'Project Alpha']);

        $response = $this->actingAs($user)->get(route('admin.inventory.index'));

        $response->assertSeeInOrder(['History Qty', 'History Project', 'Original Qty', 'Status']);
        $response->assertSeeInOrder(['>17</span>', '>Project Alpha</span>', '>42</td>'], false);
    }

    public function test_warehouse_stock_table_displays_grouped_columns_and_item_values(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create([
            'tag_number' => 'TAG-27',
            'date_received' => '2026-11-20',
            'po_sku_number' => 'PO-27',
            'manufacturer' => 'Maker',
            'model_pixel_pitch' => 'P2.5',
            'item_description' => 'Warehouse panel',
            'warehouse_location_id' => $warehouse->id,
            'sub_location' => '2Floor',
            'on_hand' => 11,
            'total_on_hand' => 12,
            'per_panel_sqm' => 1.25,
            'total_available_sqm' => 8.75,
            'available_qty' => 7,
            'available_sqm' => 8.75,
            'reservation_qty' => 4,
            'reservation_project_details' => 'Reserved client',
            'original_qty' => 20,
            'status_qty' => 6,
            'status_particular' => 'Working',
            'remarks' => 'Checked',
        ]);
        $item->histories()->create(['quantity' => 3, 'project' => 'History client']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.warehouses.show', $warehouse));

        $response->assertOk()->assertSeeInOrder([
            'Tag #', 'Date Received', 'PO / SKU No.', 'Manufacturer', 'Model / Pixel Pitch', 'Item Description',
            'Location', 'Inventory', 'Available Qty', 'Reservation', 'History', 'Original Qty', 'Status', 'Remarks',
        ]);
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);
        $cells = $xpath->query('//table/tbody/tr[td[contains(., "Warehouse panel")]]/td');
        $cellValues = array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($cells, false));
        $this->assertSame([
            'TAG-27', '2026-11-20', 'PO-27', 'Maker', 'P2.5', 'Warehouse panel', 'Marikina',
            '11', '12', '1.25', '8.75', '7 PCS', '8.75 SQM', '4', 'Reserved client',
        ], array_slice($cellValues, 0, 15));
        $this->assertStringContainsString('History client', $cells->item(15)->textContent);
        $this->assertSame(['20', '6', 'Working', 'Checked', '2Floor'], array_slice($cellValues, 16, 5));
    }

    public function test_warehouse_stock_is_paginated_by_ten_items_with_a_stable_order(): void
    {
        $this->freezeTime();
        $warehouse = WarehouseLocation::factory()->create(['name' => 'Marikina']);
        $otherWarehouse = WarehouseLocation::factory()->create();
        for ($number = 1; $number <= 21; $number++) {
            $item = InventoryItem::create([
                'item_description' => sprintf('Panel %02d', $number),
                'warehouse_location_id' => $warehouse->id,
            ]);
            if ($number === 11) {
                $item->histories()->create(['quantity' => 3, 'project' => 'Second page project']);
            }
        }
        InventoryItem::create(['item_description' => 'Other warehouse panel', 'warehouse_location_id' => $otherWarehouse->id]);
        InventoryItem::create(['item_description' => 'Deleted panel', 'warehouse_location_id' => $warehouse->id])->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.warehouses.show', $warehouse))
            ->assertSee('21 item(s)')
            ->assertSee('>Panel 21</td>', false)
            ->assertSee('>Panel 12</td>', false)
            ->assertDontSee('>Panel 11</td>', false)
            ->assertDontSee('Other warehouse panel')
            ->assertDontSee('Deleted panel')
            ->assertSee('?page=2');

        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'page' => 2]))
            ->assertSee('21 item(s)')
            ->assertSee('>Panel 11</td>', false)
            ->assertSee('>Panel 02</td>', false)
            ->assertSee('Second page project')
            ->assertDontSee('>Panel 12</td>', false)
            ->assertDontSee('>Panel 01</td>', false);

        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'page' => 3]))
            ->assertSee('>Panel 01</td>', false)
            ->assertDontSee('>Panel 02</td>', false);

        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'page' => 4]))
            ->assertSee('No items on this page.')
            ->assertDontSee('Stock tracking is not configured')
            ->assertSee('?page=3');
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
        $data['status_qty'] = -166;
        $this->put(route('admin.inventory.update', $item), $data)
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('status', 'Inventory item updated successfully.');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'item_description' => 'Updated panel',
            'warehouse_location_id' => $destination->id,
            'location' => $destination->name,
            'status_qty' => -166,
            'created_by' => $creator->id,
        ]);
        $this->get(route('admin.inventory.edit', $item))->assertSee('value="-166" type="number" min="-4294967295"', false);
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

    public function test_marikina_sub_locations_are_optional_and_displayed_with_items(): void
    {
        $user = User::factory()->create();
        $marikina = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $data = $this->itemData($marikina->id);

        $this->actingAs($user)->get(route('admin.inventory.create'))
            ->assertSeeInOrder(['1st Bldg', '2Floor', 'Main: 3Flr']);

        foreach (['1st Bldg', '2Floor', 'Main: 3Flr'] as $subLocation) {
            $data['sub_location'] = $subLocation;
            $data['item_description'] = 'Panel '.$subLocation;
            $this->post(route('admin.inventory.store'), $data)->assertSessionHasNoErrors();
            $this->assertDatabaseHas('inventory_items', [
                'warehouse_location_id' => $marikina->id,
                'item_description' => 'Panel '.$subLocation,
                'sub_location' => $subLocation,
            ]);
        }

        unset($data['sub_location']);
        $data['item_description'] = 'Unassigned panel';
        $this->post(route('admin.inventory.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory_items', ['item_description' => 'Unassigned panel', 'sub_location' => null]);

        $this->get(route('admin.inventory.index', ['search' => '2Floor']))->assertSee('Panel 2Floor')->assertDontSee('Panel 1st Bldg');
        $this->get(route('admin.warehouses.show', $marikina))->assertSee('Sub-location')->assertSee('Main: 3Flr');
    }

    public function test_marikina_sub_location_can_be_edited_and_is_cleared_on_transfer(): void
    {
        $user = User::factory()->create();
        $marikina = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $otherWarehouse = WarehouseLocation::factory()->create(['name' => 'Carmona', 'is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create([
            'item_description' => 'Panel',
            'warehouse_location_id' => $marikina->id,
            'sub_location' => '1st Bldg',
        ]);
        $data = $this->itemData($marikina->id);
        $data['sub_location'] = '2Floor';

        $this->actingAs($user)->get(route('admin.inventory.edit', $item))
            ->assertSee('value="1st Bldg" selected', false);
        $this->put(route('admin.inventory.update', $item), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'sub_location' => '2Floor']);

        $data = $this->itemData($otherWarehouse->id);
        $this->put(route('admin.inventory.update', $item), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'warehouse_location_id' => $otherWarehouse->id, 'sub_location' => null]);
    }

    public function test_invalid_or_other_warehouse_sub_locations_are_rejected(): void
    {
        $user = User::factory()->create();
        $marikina = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $otherWarehouse = WarehouseLocation::factory()->create(['name' => 'Carmona', 'is_active' => true, 'navigation_enabled' => true]);

        foreach ([[$marikina, 'Unknown'], [$otherWarehouse, '1st Bldg']] as [$warehouse, $subLocation]) {
            $data = $this->itemData($warehouse->id);
            $data['sub_location'] = $subLocation;

            $this->actingAs($user)->from(route('admin.inventory.create'))
                ->post(route('admin.inventory.store'), $data)
                ->assertRedirect(route('admin.inventory.create'))
                ->assertSessionHasErrors('sub_location');
        }

        $this->assertDatabaseCount('inventory_items', 0);
    }

    public function test_ohaap_floor_options_are_saved_and_other_warehouse_choices_are_rejected(): void
    {
        $user = User::factory()->create();
        $ohaap = WarehouseLocation::factory()->create(['name' => 'Ohaap', 'is_active' => true, 'navigation_enabled' => true]);
        $marikina = WarehouseLocation::factory()->create(['name' => 'Marikina', 'is_active' => true, 'navigation_enabled' => true]);
        $data = $this->itemData($ohaap->id);

        $this->actingAs($user)->get(route('admin.inventory.create'))
            ->assertSeeInOrder(['1st Flr', '2nd Flr', '3rd Flr', '4th Flr', '5th Flr']);

        foreach (['1st Flr', '2nd Flr', '3rd Flr', '4th Flr', '5th Flr'] as $floor) {
            $data['sub_location'] = $floor;
            $data['item_description'] = 'Ohaap panel '.$floor;
            $this->post(route('admin.inventory.store'), $data)->assertSessionHasNoErrors();
            $this->assertDatabaseHas('inventory_items', ['item_description' => 'Ohaap panel '.$floor, 'sub_location' => $floor]);
        }

        $this->get(route('admin.warehouses.show', $ohaap))->assertSee('Sub-location')->assertSee('Ohaap panel 3rd Flr');
        $item = InventoryItem::where('item_description', 'Ohaap panel 3rd Flr')->firstOrFail();
        $this->get(route('admin.inventory.edit', $item))->assertSee('value="3rd Flr" selected', false);
        $data = $this->itemData($marikina->id);
        $data['sub_location'] = '3rd Flr';
        $this->from(route('admin.inventory.edit', $item))
            ->put(route('admin.inventory.update', $item), $data)
            ->assertSessionHasErrors('sub_location');
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'warehouse_location_id' => $ohaap->id, 'sub_location' => '3rd Flr']);
    }

    public function test_multiple_project_histories_can_be_added_edited_and_viewed_in_every_warehouse(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $data = $this->itemData($warehouse->id);
        $data['history_submitted'] = '1';
        $data['histories'] = [
            ['quantity' => 15, 'project' => 'Client Alpha'],
            ['quantity' => 36, 'project' => 'Client Beta'],
            ['quantity' => 0, 'project' => 'Client Gamma'],
        ];

        $this->actingAs($user)->post(route('admin.inventory.store'), $data)->assertSessionHasNoErrors();
        $item = InventoryItem::where('item_description', 'LED panel')->firstOrFail();
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'quantity' => 15, 'project' => 'Client Alpha']);
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'quantity' => 36, 'project' => 'Client Beta']);
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'quantity' => 0, 'project' => 'Client Gamma']);
        $this->get(route('admin.inventory.index'))->assertSeeInOrder(['Client Alpha', 'Client Beta', 'Client Gamma']);
        $this->get(route('admin.warehouses.show', $warehouse))->assertSeeInOrder(['Client Alpha', 'Client Beta', 'Client Gamma']);
        $this->get(route('admin.inventory.edit', $item))->assertSee('Client Beta');

        $data['histories'] = [['quantity' => 22, 'project' => 'Updated client']];
        $this->put(route('admin.inventory.update', $item), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('inventory_item_histories', 1);
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'quantity' => 22, 'project' => 'Updated client']);
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'history_qty' => 22, 'history_project' => 'Updated client']);

        unset($data['histories']);
        $this->put(route('admin.inventory.update', $item), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('inventory_item_histories', 0);
    }

    public function test_inventory_and_warehouse_lists_collapse_additional_histories_per_item(): void
    {
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $multiple = InventoryItem::create(['item_description' => 'Multiple panel', 'warehouse_location_id' => $warehouse->id]);
        $multiple->histories()->createMany([
            ['quantity' => 15, 'project' => 'Client Alpha'],
            ['quantity' => 36, 'project' => 'Client Beta'],
            ['quantity' => 0, 'project' => 'Client Gamma'],
        ]);
        $single = InventoryItem::create(['item_description' => 'Single panel', 'warehouse_location_id' => $warehouse->id]);
        $single->histories()->create(['quantity' => 9, 'project' => 'Client Solo']);
        InventoryItem::create(['item_description' => 'Empty panel', 'warehouse_location_id' => $warehouse->id]);
        $this->actingAs(User::factory()->create());

        foreach ([route('admin.inventory.index'), route('admin.warehouses.show', $warehouse)] as $url) {
            $response = $this->get($url);
            $response->assertOk();
            $document = new DOMDocument;
            $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new DOMXPath($document);

            $details = $xpath->query('//tr[td[contains(., "Multiple panel")]]//details[not(@open)]');
            $this->assertCount(1, $details);
            $this->assertStringContainsString('Additional histories (2)', $details->item(0)->textContent);
            $this->assertStringContainsString('Client Beta', $details->item(0)->textContent);
            $this->assertStringContainsString('Client Gamma', $details->item(0)->textContent);
            $this->assertStringNotContainsString('Client Alpha', $details->item(0)->textContent);
            $this->assertCount(1, $xpath->query('//tr[td[contains(., "Multiple panel")]]//details/preceding-sibling::div[contains(., "Client Alpha")]'));
            $this->assertCount(0, $xpath->query('//tr[td[contains(., "Single panel") or contains(., "Empty panel")]]//details'));
            $response->assertSee('Client Solo');
        }
    }

    public function test_invalid_history_update_preserves_item_and_existing_history(): void
    {
        $user = User::factory()->create();
        $warehouse = WarehouseLocation::factory()->create(['is_active' => true, 'navigation_enabled' => true]);
        $item = InventoryItem::create(['item_description' => 'Original', 'warehouse_location_id' => $warehouse->id]);
        $item->histories()->create(['quantity' => 4, 'project' => 'Existing client']);
        $data = $this->itemData($warehouse->id);
        $data['history_submitted'] = '1';
        $data['histories'] = [['quantity' => -1, 'project' => 'Invalid client']];

        $this->actingAs($user)->from(route('admin.inventory.edit', $item))
            ->put(route('admin.inventory.update', $item), $data)
            ->assertSessionHasErrors('histories.0.quantity');

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'item_description' => 'Original']);
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'quantity' => 4, 'project' => 'Existing client']);
    }

    public function test_existing_inventory_histories_are_preserved_by_migration(): void
    {
        $migration = require database_path('migrations/2026_11_23_140000_create_inventory_item_histories_table.php');
        $migration->down();
        $item = InventoryItem::create(['item_description' => 'Legacy panel', 'history_qty' => 15, 'history_project' => 'Legacy client']);
        $deleted = InventoryItem::create(['item_description' => 'Deleted panel', 'history_qty' => 36, 'history_project' => 'Deleted client']);
        $deleted->delete();

        $migration->up();

        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $item->id, 'quantity' => 15, 'project' => 'Legacy client']);
        $this->assertDatabaseHas('inventory_item_histories', ['inventory_item_id' => $deleted->id, 'quantity' => 36, 'project' => 'Deleted client']);
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
