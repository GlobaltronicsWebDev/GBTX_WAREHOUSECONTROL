<?php

namespace Tests\Feature\Admin;

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
        $this->assertDatabaseCount('warehouse_locations', 1);
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
        $this->assertDatabaseCount('warehouse_locations', 4);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        foreach (['Globaltronics', 'Ohaap', 'Marikina', 'Carmona'] as $name) {
            $response->assertSee($name);
        }
    }

    public function test_guest_is_redirected_to_login_from_a_warehouse_dashboard(): void
    {
        $location = WarehouseLocation::factory()->create();

        $this->get(route('admin.warehouses.show', $location))
            ->assertRedirect(route('login'));
    }
}
