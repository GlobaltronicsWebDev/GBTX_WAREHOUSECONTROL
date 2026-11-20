<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_and_update_their_profile(): void
    {
        $department = Department::factory()->create(['name' => 'MIS']);
        $company = Company::factory()->create(['name' => 'Globaltronics, Inc.']);
        $user = User::factory()->create([
            'name' => 'Paz User',
            'department_id' => $department->id,
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.settings.profile'));

        $response->assertSee('Profile Settings');
        $response->assertSee('MIS');
        $response->assertSee('Globaltronics, Inc.');

        $this->actingAs($user)->put(route('admin.settings.profile.update'), [
            'name' => 'Paz Updated',
            'email' => 'unexpected@example.com',
        ])->assertRedirect(route('admin.settings.profile'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Paz Updated',
            'email' => $user->email,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_profile_settings(): void
    {
        $this->get(route('admin.settings.profile'))->assertRedirect('/login');
    }

    public function test_security_page_requires_password_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.security'))
            ->assertRedirect(route('password.confirm'));

        $this->get(route('password.confirm'))->assertSee('Confirm password');
    }

    public function test_incorrect_password_does_not_confirm_the_session(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass123!'),
        ]);

        $this->actingAs($user)->post(route('password.confirm.store'), [
            'password' => 'WrongPass123!',
        ])->assertSessionHasErrors('password');

        $this->assertFalse(session()->has('auth.password_confirmed_at'));
    }

    public function test_confirmed_user_can_open_security_and_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass123!'),
        ]);

        $this->actingAs($user)->get(route('admin.settings.security'))
            ->assertRedirect(route('password.confirm'));

        $this->post(route('password.confirm.store'), [
            'password' => 'CurrentPass123!',
        ])->assertRedirect(route('admin.settings.security'));

        $this->get(route('admin.settings.security'))->assertSee('Change password');

        $this->put(route('admin.settings.security.password'), [
            'password' => 'UpdatedPass123!',
            'password_confirmation' => 'UpdatedPass123!',
        ])->assertRedirect(route('admin.settings.security'));

        $this->assertTrue(Hash::check('UpdatedPass123!', $user->fresh()->password));
    }
}
