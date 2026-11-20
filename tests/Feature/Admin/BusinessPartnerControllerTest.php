<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessPartner;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessPartnerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_admin_can_create_a_client(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());

        $response = $this->actingAs($admin)->post(route('admin.settings.partners.store'), [
            'name' => 'Globe Telecom',
            'type' => 'client',
            'category' => 'Telecommunications',
            'address' => 'Makati City, Metro Manila',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.partners'));
        $this->assertDatabaseHas('business_partners', [
            'name' => 'Globe Telecom',
            'type' => 'client',
            'category' => 'Telecommunications',
            'address' => 'Makati City, Metro Manila',
            'is_active' => true,
        ]);
    }

    public function test_partner_listing_filters_by_type_status_and_search(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        BusinessPartner::factory()->create(['name' => 'Aboitiz Equity Ventures', 'type' => 'supplier', 'is_active' => true]);
        BusinessPartner::factory()->create(['name' => 'Globe Telecom', 'type' => 'client', 'is_active' => true]);
        BusinessPartner::factory()->create(['name' => 'Inactive Client', 'type' => 'client', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.settings.partners', [
            'search' => 'Globe',
            'type' => 'client',
            'status' => 'active',
        ]));

        $response->assertSee('Globe Telecom');
        $response->assertDontSee('Aboitiz Equity Ventures');
        $response->assertDontSee('Inactive Client');
    }

    public function test_partner_can_be_updated_and_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $partner = BusinessPartner::factory()->create();

        $this->actingAs($admin)->put(route('admin.settings.partners.update', $partner), [
            'name' => 'Updated Partner',
            'type' => 'client',
            'category' => '',
            'address' => '',
            'is_active' => '0',
        ])->assertRedirect(route('admin.settings.partners'));

        $this->assertDatabaseHas('business_partners', [
            'id' => $partner->id,
            'name' => 'Updated Partner',
            'type' => 'client',
            'category' => null,
            'address' => null,
            'is_active' => false,
        ]);

        $this->actingAs($admin)->delete(route('admin.settings.partners.destroy', $partner))
            ->assertRedirect(route('admin.settings.partners'));

        $this->assertDatabaseMissing('business_partners', ['id' => $partner->id]);
    }

    public function test_partner_type_must_be_supplier_or_client(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());

        $response = $this->actingAs($admin)->from(route('admin.settings.partners'))->post(route('admin.settings.partners.store'), [
            'name' => 'Invalid Type Partner',
            'type' => 'unknown',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.partners'));
        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('business_partners', 0);
    }
}
