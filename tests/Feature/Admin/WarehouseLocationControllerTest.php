<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WarehouseLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseLocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_temporary_sticky_header_snapshot(): void
    {
        $warehouse = WarehouseLocation::where('name', 'A Juan')->firstOrFail();
        for ($number = 1; $number <= 10; $number++) {
            $item = InventoryItem::create(['item_description' => 'Panel '.$number, 'warehouse_location_id' => $warehouse->id]);
            $item->histories()->create(['quantity' => $number, 'project' => str_repeat("A project detail\n", 8)]);
        }
        $response = $this->actingAs(User::factory()->create())->get(route('admin.warehouses.show', $warehouse));
        $response->assertOk();
        $html = str_replace(config('app.url'), 'http://127.0.0.1:9', $response->getContent());
        file_put_contents(getenv('WAREHOUSE_RENDER_SNAPSHOT'), $html);
    }

    public function test_it_admin_can_create_a_warehouse_location(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());

        $response = $this->actingAs($admin)->post(route('admin.settings.locations.store'), [
            'name' => 'DBI Warehouse',
            'entity' => 'Warehouse',
            'is_active' => '1',
            'navigation_enabled' => '0',
        ]);

        $response->assertRedirect(route('admin.settings.locations'));
        $this->assertDatabaseHas('warehouse_locations', [
            'name' => 'DBI Warehouse',
            'entity' => 'Warehouse',
            'is_active' => true,
            'navigation_enabled' => false,
        ]);
    }

    public function test_location_name_is_unique_within_its_entity(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        WarehouseLocation::factory()->create(['name' => 'DBI Warehouse', 'entity' => 'Warehouse']);

        $response = $this->actingAs($admin)->from(route('admin.settings.locations'))->post(route('admin.settings.locations.store'), [
            'name' => 'DBI Warehouse',
            'entity' => 'Warehouse',
            'is_active' => '1',
            'navigation_enabled' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.locations'));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('warehouse_locations', 3);
    }

    public function test_location_listing_filters_by_search_and_status(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        WarehouseLocation::factory()->create(['name' => 'DBI Warehouse', 'is_active' => true]);
        WarehouseLocation::factory()->create(['name' => 'Inactive Storage', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.settings.locations', [
            'search' => 'DBI',
            'status' => 'active',
        ]));

        $response->assertSee('DBI Warehouse');
        $response->assertSee('Warehouse');
        $response->assertDontSee('Inactive Storage');
    }

    public function test_location_can_be_updated_and_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $location = WarehouseLocation::factory()->create();

        $this->actingAs($admin)->put(route('admin.settings.locations.update', $location), [
            'name' => 'Main Storage',
            'entity' => 'Storage',
            'is_active' => '0',
            'navigation_enabled' => '0',
        ])->assertRedirect(route('admin.settings.locations'));

        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'name' => 'Main Storage',
            'entity' => 'Storage',
            'is_active' => false,
            'navigation_enabled' => false,
        ]);

        $this->actingAs($admin)->delete(route('admin.settings.locations.destroy', $location))
            ->assertRedirect(route('admin.settings.locations'));

        $this->assertDatabaseMissing('warehouse_locations', ['id' => $location->id]);
    }

    public function test_warehouse_admin_can_open_an_active_warehouse_dashboard(): void
    {
        $warehouseAdmin = User::factory()->create();
        $warehouseAdmin->roles()->attach(Role::where('slug', 'warehouse-admin')->firstOrFail());
        $location = WarehouseLocation::factory()->create(['name' => 'Marikina']);
        WarehouseLocation::factory()->create([
            'name' => 'Hidden Warehouse',
            'navigation_enabled' => false,
        ]);

        $response = $this->actingAs($warehouseAdmin)->get(route('admin.warehouses.show', $location));

        $response->assertSee('Marikina Warehouse');
        $response->assertSee('Stock tracking is not configured');
        $response->assertSee(route('admin.warehouses.show', $location));
        $response->assertSee('aria-current="page"', false);
        $response->assertDontSee('Hidden Warehouse');
    }

    public function test_inactive_warehouse_dashboard_returns_not_found(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $location = WarehouseLocation::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.warehouses.show', $location))
            ->assertNotFound();
    }

    public function test_warehouse_dashboard_returns_not_found_when_navigation_is_disabled(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $location = WarehouseLocation::factory()->create(['navigation_enabled' => false]);

        $this->actingAs($admin)
            ->get(route('admin.warehouses.show', $location))
            ->assertNotFound();
    }

    public function test_warehouse_location_seeder_populates_the_menu_without_duplicates(): void
    {
        $this->seed(WarehouseLocationSeeder::class);
        $this->seed(WarehouseLocationSeeder::class);
        $this->assertDatabaseCount('warehouse_locations', 6);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeInOrder(['A Juan', 'Clark Office', 'Globaltronics', 'Ohaap', 'Marikina', 'Carmona']);
    }

    public function test_sub_location_menu_filters_items_and_preserves_pagination(): void
    {
        $this->freezeTime();
        $warehouse = WarehouseLocation::where('name', 'A Juan')->firstOrFail();
        $otherWarehouse = WarehouseLocation::factory()->create(['name' => 'Marikina']);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'warehouse-admin')->firstOrFail());
        for ($number = 1; $number <= 11; $number++) {
            InventoryItem::create([
                'item_description' => sprintf('First floor panel %02d', $number),
                'warehouse_location_id' => $warehouse->id,
                'sub_location' => 'A JUAN MAIN 1ST FLOOR',
            ]);
        }
        InventoryItem::create(['item_description' => 'Third floor panel', 'warehouse_location_id' => $warehouse->id, 'sub_location' => 'A JUAN MAIN 3RD FLOOR']);
        InventoryItem::create(['item_description' => 'Unassigned panel', 'warehouse_location_id' => $warehouse->id]);
        InventoryItem::create(['item_description' => 'Other warehouse panel', 'warehouse_location_id' => $otherWarehouse->id, 'sub_location' => 'A JUAN MAIN 1ST FLOOR']);
        $firstFloorUrl = route('admin.warehouses.show', ['location' => $warehouse, 'sub_location' => 'A JUAN MAIN 1ST FLOOR']);

        $response = $this->actingAs($admin)->get($firstFloorUrl);
        $response
            ->assertSee($firstFloorUrl)
            ->assertSee('A JUAN MAIN 3RD FLOOR')
            ->assertSee('11 item(s)')
            ->assertSee('First floor panel 11')
            ->assertSee('First floor panel 02')
            ->assertDontSee('First floor panel 01')
            ->assertDontSee('Third floor panel')
            ->assertDontSee('Unassigned panel')
            ->assertDontSee('Other warehouse panel')
            ->assertDontSee('Delete selected items')
            ->assertDontSee('Delete all warehouse items')
            ->assertDontSee('aria-label="Delete First floor panel', false)
            ->assertSee('sub_location=A%20JUAN%20MAIN%201ST%20FLOOR&amp;page=2', false);
        $document = new \DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $activeLinks = (new \DOMXPath($document))->query('//a[@aria-current="page"]');
        $this->assertSame($firstFloorUrl, $activeLinks->item(0)->getAttribute('href'));

        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'sub_location' => 'A JUAN MAIN 1ST FLOOR', 'page' => 2]))
            ->assertSee('First floor panel 01')
            ->assertDontSee('First floor panel 02');

        $this->get(route('admin.warehouses.show', $warehouse))
            ->assertSee('13 item(s)')
            ->assertSee('Third floor panel')
            ->assertSee('Unassigned panel')
            ->assertSee('Delete all warehouse items');
    }

    public function test_sub_location_filter_rejects_unknown_locations_and_shows_empty_locations(): void
    {
        $warehouse = WarehouseLocation::where('name', 'A Juan')->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.warehouses.show', ['location' => $warehouse, 'sub_location' => 'A JUAN MAIN 3RD FLOOR']))
            ->assertSee('No items found in this sub-location.');
        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'sub_location' => 'Unknown']))
            ->assertNotFound();
        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'sub_location' => ['A JUAN MAIN 1ST FLOOR']]))
            ->assertNotFound();
    }

    public function test_warehouse_search_filters_inventory_within_sub_location_and_preserves_pagination(): void
    {
        $this->freezeTime();
        $warehouse = WarehouseLocation::where('name', 'A Juan')->firstOrFail();
        $otherWarehouse = WarehouseLocation::factory()->create();
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'warehouse-admin')->firstOrFail());
        for ($number = 1; $number <= 11; $number++) {
            InventoryItem::create([
                'tag_number' => sprintf('MATCH-%02d', $number),
                'item_description' => sprintf('Panel %02d', $number),
                'warehouse_location_id' => $warehouse->id,
                'sub_location' => 'A JUAN MAIN 1ST FLOOR',
            ]);
        }
        InventoryItem::create(['tag_number' => 'MATCH-OTHER-FLOOR', 'item_description' => 'Other floor', 'warehouse_location_id' => $warehouse->id, 'sub_location' => 'A JUAN MAIN 3RD FLOOR']);
        InventoryItem::create(['tag_number' => 'MATCH-OTHER-WAREHOUSE', 'item_description' => 'Other warehouse', 'warehouse_location_id' => $otherWarehouse->id]);
        $params = ['location' => $warehouse, 'sub_location' => 'A JUAN MAIN 1ST FLOOR', 'search' => 'MATCH'];
        $clearUrl = route('admin.warehouses.show', ['location' => $warehouse, 'sub_location' => 'A JUAN MAIN 1ST FLOOR']);

        $this->actingAs($user)->get(route('admin.warehouses.show', $params))
            ->assertSee('11 item(s)')
            ->assertSee('Panel 11')
            ->assertDontSee('Panel 01')
            ->assertDontSee('Other floor')
            ->assertDontSee('Other warehouse')
            ->assertSee('name="sub_location" value="A JUAN MAIN 1ST FLOOR"', false)
            ->assertSee('name="search" value="MATCH"', false)
            ->assertSee('href="'.$clearUrl.'"', false)
            ->assertSee('sub_location=A%20JUAN%20MAIN%201ST%20FLOOR&amp;search=MATCH&amp;page=2', false)
            ->assertDontSee('Delete all warehouse items');
        $this->get(route('admin.warehouses.show', array_merge($params, ['page' => 2])))
            ->assertSee('Panel 01')
            ->assertDontSee('Panel 02');
    }

    public function test_warehouse_search_handles_no_matches_and_keeps_bulk_delete_safely_scoped(): void
    {
        $warehouse = WarehouseLocation::where('name', 'A Juan')->firstOrFail();
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        InventoryItem::create(['item_description' => 'Panel 0', 'warehouse_location_id' => $warehouse->id]);
        InventoryItem::create(['item_description' => 'Other panel', 'warehouse_location_id' => $warehouse->id]);

        $this->actingAs($user)->get(route('admin.warehouses.show', ['location' => $warehouse, 'search' => '0']))
            ->assertSee('Panel 0')
            ->assertDontSee('Other panel')
            ->assertSee('1 item(s)')
            ->assertSee('Delete selected items')
            ->assertDontSee('Delete all warehouse items');
        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'search' => 'missing']))
            ->assertSee('No inventory items match your search.')
            ->assertDontSee('Stock tracking is not configured');
        $this->get(route('admin.warehouses.show', ['location' => $warehouse, 'search' => '<script>']))
            ->assertSee('value="&lt;script&gt;"', false);
        $this->from(route('admin.warehouses.show', $warehouse))
            ->get(route('admin.warehouses.show', ['location' => $warehouse, 'search' => ['invalid']]))
            ->assertSessionHasErrors('search');
        $this->get(route('admin.warehouses.show', $warehouse))->assertSee('Delete all warehouse items');
    }

    public function test_guest_is_redirected_to_login_from_a_warehouse_dashboard(): void
    {
        $location = WarehouseLocation::factory()->create();

        $this->get(route('admin.warehouses.show', $location))
            ->assertRedirect(route('login'));
    }
}
