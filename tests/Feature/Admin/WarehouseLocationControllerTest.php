<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RoleSeeder;
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
}
