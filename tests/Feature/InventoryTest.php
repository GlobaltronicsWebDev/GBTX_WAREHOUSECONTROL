<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_inventory_and_is_redirected(): void
    {
        $response = $this->get('/admin/inventory');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_inventory_with_eol_philips_units(): void
    {
        $user = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($itRole);

        $item = InventoryItem::factory()->create([
            'category' => 'EOL PHILIPS UNITS',
            'manufacturer' => 'Philips',
            'check_in_date' => '2026-09-15',
            'model' => '55BDL4050D/00',
            'item_description' => '55" D-Line Android Signage Display',
            'quantity' => 12,
            'location' => 'Globaltronics',
        ]);

        $response = $this->actingAs($user)->get('/admin/inventory?category=EOL+PHILIPS+UNITS');

        $response->assertStatus(200);
        $response->assertSee('EOL PHILIPS UNITS');
        $response->assertSee('MANUFACTURER');
        $response->assertSee('CHECK IN DATE');
        $response->assertSee('MODEL');
        $response->assertSee('ITEM DESCRIPTION');
        $response->assertSee('QTY');
        $response->assertSee('55BDL4050D/00');
        $response->assertSee('Philips');
    }

    public function test_authenticated_user_can_view_centralized_led_inventory(): void
    {
        $user = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($itRole);

        $item = InventoryItem::factory()->create([
            'category' => 'CENTRALIZED LED INVENTORY',
            'tag_number' => 'TAG-LED-001',
            'po_number' => 'PO-2026-0814',
            'manufacturer' => 'GLOBALTRONICS',
            'check_in_date' => '2026-08-14',
            'model' => 'P2.5 Indoor',
            'item_description' => 'Die-Cast Aluminum Cabinet 500x500mm',
            'quantity' => 60,
            'sqm' => 15.00,
            'location' => 'Globaltronics',
        ]);

        $response = $this->actingAs($user)->get('/admin/inventory?category=CENTRALIZED+LED+INVENTORY');

        $response->assertStatus(200);
        $response->assertSee('CENTRALIZED LED INVENTORY');
        $response->assertSee('TAG #');
        $response->assertSee('DATE RECEIVED');
        $response->assertSee('PO / SKU No.');
        $response->assertSee('MANUFACTURER');
        $response->assertSee('MODEL / PIXEL PITCH');
        $response->assertSee('ITEM DESCRIPTION');
        $response->assertSee('AVAILABLE QTY');
        $response->assertSee('SQM');
        $response->assertSee('TAG-LED-001');
        $response->assertSee('PO-2026-0814');
        $response->assertSee('P2.5 Indoor');
        $response->assertSee('15.00');
    }

    public function test_warehouse_admin_can_access_and_view_inventory(): void
    {
        $warehouseAdmin = User::factory()->create();
        $warehouseRole = Role::where('slug', 'warehouse-admin')->first();
        $warehouseAdmin->roles()->attach($warehouseRole);

        $response = $this->actingAs($warehouseAdmin)->get('/admin/inventory');

        $response->assertStatus(200);
        $response->assertSee('Warehouse Inventory System');
        $response->assertSee('CENTRALIZED LED INVENTORY');
    }

    public function test_user_can_add_new_inventory_item(): void
    {
        $user = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($itRole);

        $response = $this->actingAs($user)->post('/admin/inventory', [
            'category' => 'EOL PHILIPS UNITS',
            'manufacturer' => 'Philips',
            'check_in_date' => '2026-10-05',
            'model' => '65BDL3552T/00',
            'item_description' => '65" Interactive Multi-Touch 4K Display Panel',
            'quantity' => 8,
            'location' => 'Globaltronics',
            'status' => 'in_stock',
        ]);

        $response->assertRedirect(route('admin.inventory.index', ['category' => 'EOL PHILIPS UNITS']));
        $this->assertDatabaseHas('inventory_items', [
            'model' => '65BDL3552T/00',
            'manufacturer' => 'Philips',
            'quantity' => 8,
            'location' => 'Globaltronics',
            'created_by' => $user->id,
        ]);
    }

    public function test_user_can_add_centralized_led_item(): void
    {
        $user = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($itRole);

        $response = $this->actingAs($user)->post('/admin/inventory', [
            'category' => 'CENTRALIZED LED INVENTORY',
            'tag_number' => 'TAG-LED-099',
            'po_number' => 'PO-2026-9999',
            'manufacturer' => 'GLOBALTRONICS',
            'check_in_date' => '2026-10-05',
            'model' => 'P3.91 Outdoor Pro',
            'item_description' => '500x1000mm IP65 LED Rental Panel',
            'quantity' => 32,
            'sqm' => 16.00,
            'location' => 'AJUAN',
            'status' => 'in_stock',
        ]);

        $response->assertRedirect(route('admin.inventory.index', ['category' => 'CENTRALIZED LED INVENTORY']));
        $this->assertDatabaseHas('inventory_items', [
            'tag_number' => 'TAG-LED-099',
            'po_number' => 'PO-2026-9999',
            'model' => 'P3.91 Outdoor Pro',
            'manufacturer' => 'GLOBALTRONICS',
            'quantity' => 32,
            'sqm' => 16.00,
            'location' => 'AJUAN',
            'created_by' => $user->id,
        ]);
    }

    public function test_user_can_update_inventory_item(): void
    {
        $user = User::factory()->create();
        $item = InventoryItem::factory()->create([
            'category' => 'EOL PHILIPS UNITS',
            'manufacturer' => 'Philips',
            'model' => 'OLD-MODEL-100',
            'quantity' => 5,
            'location' => 'Globaltronics',
        ]);

        $response = $this->actingAs($user)->put("/admin/inventory/{$item->id}", [
            'category' => 'EOL PHILIPS UNITS',
            'manufacturer' => 'Philips',
            'check_in_date' => '2026-10-06',
            'model' => 'NEW-MODEL-200',
            'item_description' => 'Updated Commercial Panel',
            'quantity' => 15,
            'location' => 'AJUAN',
            'status' => 'in_stock',
        ]);

        $response->assertRedirect(route('admin.inventory.index', ['category' => 'EOL PHILIPS UNITS']));
        $item->refresh();

        $this->assertSame('NEW-MODEL-200', $item->model);
        $this->assertSame(15, $item->quantity);
        $this->assertSame('AJUAN', $item->location);
    }

    public function test_user_can_delete_inventory_item(): void
    {
        $user = User::factory()->create();
        $item = InventoryItem::factory()->create([
            'category' => 'EOL PHILIPS UNITS',
            'model' => 'DELETE-ME-MODEL',
        ]);

        $response = $this->actingAs($user)->delete("/admin/inventory/{$item->id}");

        $response->assertRedirect(route('admin.inventory.index', ['category' => 'EOL PHILIPS UNITS']));
        $this->assertDatabaseMissing('inventory_items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_can_search_inventory_by_model(): void
    {
        $user = User::factory()->create();

        InventoryItem::factory()->create([
            'model' => 'ALPHA-SEARCH-999',
            'item_description' => 'Unique search term alpha',
        ]);

        $response = $this->actingAs($user)->get('/admin/inventory?search=ALPHA-SEARCH-999');

        $response->assertStatus(200);
        $response->assertSee('ALPHA-SEARCH-999');
        $response->assertDontSee('BETA-OTHER-888');
    }

    public function test_user_can_view_service_units_events_demo_category_and_subcategories(): void
    {
        $user = User::factory()->create();
        $warehouseRole = Role::where('slug', 'warehouse-admin')->first();
        $user->roles()->attach($warehouseRole);

        InventoryItem::factory()->create([
            'category' => 'LED Service Units',
            'manufacturer' => 'GLOBALTRONICS',
            'model' => 'SERVICE-LED-P3',
            'quantity' => 10,
        ]);

        InventoryItem::factory()->create([
            'category' => 'Kiosks',
            'manufacturer' => 'PHILIPS',
            'model' => 'SERVICE-KIOSK-55',
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventory.index', ['category' => 'Service Units (Events, Demo)']));

        $response->assertStatus(200);
        $response->assertSee('SERVICE UNITS (EVENTS, DEMO)');
        $response->assertSee('LED Service Units');
        $response->assertSee('Philips Service Units');
        $response->assertSee('Video Controllers / Processors');
        $response->assertSee('Shuttle');
        $response->assertSee('Aver');
        $response->assertSee('Digital iPoster');
        $response->assertSee('Kiosks');
        $response->assertSee('SERVICE-LED-P3');
        $response->assertSee('SERVICE-KIOSK-55');
    }

    public function test_user_can_add_service_units_subcategory_item(): void
    {
        $user = User::factory()->create();
        $itRole = Role::where('slug', 'it-admin')->first();
        $user->roles()->attach($itRole);

        $response = $this->actingAs($user)->post('/admin/inventory', [
            'category' => 'Video Controllers / Processors',
            'manufacturer' => 'NOVASTAR',
            'check_in_date' => '2026-10-06',
            'model' => 'VX4S-PRO',
            'item_description' => '4K Video Processor and Scaler Unit for Event Staging',
            'quantity' => 4,
            'location' => 'Globaltronics',
            'status' => 'in_stock',
        ]);

        $response->assertRedirect(route('admin.inventory.index', ['category' => 'Video Controllers / Processors']));
        $this->assertDatabaseHas('inventory_items', [
            'category' => 'Video Controllers / Processors',
            'model' => 'VX4S-PRO',
            'manufacturer' => 'NOVASTAR',
            'quantity' => 4,
        ]);
    }
}
