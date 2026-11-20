<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_admin_can_create_a_department(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());

        $response = $this->actingAs($admin)->post(route('admin.settings.departments.store'), [
            'name' => 'MIS',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.departments'));
        $this->assertDatabaseHas('departments', ['name' => 'MIS', 'is_active' => true]);
    }

    public function test_department_listing_filters_status_and_shows_assigned_user_count(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $department = Department::factory()->create(['name' => 'MIS', 'is_active' => true]);
        Department::factory()->create(['name' => 'Warehouse', 'is_active' => false]);
        User::factory()->create(['department_id' => $department->id]);

        $response = $this->actingAs($admin)->get(route('admin.settings.departments', [
            'search' => 'MIS',
            'status' => 'active',
        ]));

        $response->assertSee('MIS');
        $response->assertSee('1 User');
        $response->assertDontSee('Warehouse');
    }

    public function test_deleting_a_department_unassigns_its_users(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'it-admin')->firstOrFail());
        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->actingAs($admin)->delete(route('admin.settings.departments.destroy', $department))
            ->assertRedirect(route('admin.settings.departments'));

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'department_id' => null]);
    }

    public function test_non_it_admin_cannot_manage_departments(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.departments'))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('error');
    }
}
