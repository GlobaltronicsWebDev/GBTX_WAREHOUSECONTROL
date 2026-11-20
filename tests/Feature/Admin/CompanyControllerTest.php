<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_admin_can_create_a_company(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());

        $response = $this->actingAs($admin)->post(route('admin.settings.companies.store'), [
            'name' => 'Globaltronics, Inc.',
            'code' => 'global',
            'contact_phone' => '+63 2 8723 4461',
            'contact_email' => 'info@globaltronics.net',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.companies'));
        $this->assertDatabaseHas('companies', [
            'name' => 'Globaltronics, Inc.',
            'code' => 'GLOBAL',
            'contact_phone' => '+63 2 8723 4461',
            'contact_email' => 'info@globaltronics.net',
            'is_active' => true,
        ]);
    }

    public function test_company_code_must_be_unique_case_insensitively(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        Company::factory()->create(['code' => 'GLOBAL']);

        $response = $this->actingAs($admin)->from(route('admin.settings.companies'))->post(route('admin.settings.companies.store'), [
            'name' => 'Duplicate Company',
            'code' => 'global',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.companies'));
        $response->assertSessionHasErrors('code');
        $this->assertDatabaseCount('companies', 1);
    }

    public function test_it_admin_can_update_company_details_and_status(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $company = Company::factory()->create(['code' => 'OLD']);

        $response = $this->actingAs($admin)->put(route('admin.settings.companies.update', $company), [
            'name' => 'Updated Company',
            'code' => 'new-code',
            'contact_phone' => '',
            'contact_email' => '',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.settings.companies'));
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Company',
            'code' => 'NEW-CODE',
            'contact_phone' => null,
            'contact_email' => null,
            'is_active' => false,
        ]);
    }

    public function test_it_admin_can_delete_a_company(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $company = Company::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.settings.companies.destroy', $company));

        $response->assertRedirect(route('admin.settings.companies'));
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_company_list_filters_by_search_and_status(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        Company::factory()->create(['name' => 'DB International', 'code' => 'DBI', 'is_active' => true]);
        Company::factory()->create(['name' => 'Globaltronics', 'code' => 'GLOBAL', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.settings.companies', [
            'search' => 'DBI',
            'status' => 'active',
        ]));

        $response->assertSee('DB International');
        $response->assertDontSee('Globaltronics');
    }

}
