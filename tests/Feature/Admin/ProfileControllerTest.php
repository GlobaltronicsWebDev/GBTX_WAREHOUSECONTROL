<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\ProfileDirectorySeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
        $response->assertSee('Edit Profile');
        $response->assertSee('Edit Account');
        $response->assertSee('Primary navigation');
        $response->assertSee('Settings navigation');
        $response->assertSee('My Dashboard');
        $response->assertSee('MIS');
        $response->assertSee('Globaltronics, Inc.');

        $this->actingAs($user)->put(route('admin.settings.profile.update'), [
            'settings_modal' => 'profile',
            'first_name' => 'Paz',
            'last_name' => 'Updated',
            'company_id' => $company->id,
            'department_id' => $department->id,
        ])->assertRedirect(route('admin.settings.profile'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Paz Updated',
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);
    }

    public function test_profile_edit_lists_the_requested_departments_and_companies(): void
    {
        $this->seed(ProfileDirectorySeeder::class);
        $this->seed(ProfileDirectorySeeder::class);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.settings.profile'));

        foreach ([
            'Executive',
            'Admin',
            'Sales',
            'TSD',
            'DBE',
            'Accounting',
            'Audit',
            'HRAD',
            'Purchasing',
            'Warehouse',
            'MIS',
            'VISMIN',
            'DB International Sales & Services, Inc.',
            'General',
            'GGC Foundation',
            'Globaltronics, Inc.',
            'Glory International Technology Inc.',
            'Nutriceutical',
            'Organic Coconut Association of The Philippines',
            'Philippine Shanghai Chamber Of Commerce and Industry Inc.',
            'TeamAsia Corp.',
            'The Parc Foundations',
        ] as $option) {
            $response->assertSeeText($option);
        }

        $this->assertDatabaseCount('departments', 12);
        $this->assertDatabaseCount('companies', 10);
    }

    public function test_user_can_update_their_username_and_email(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'username' => 'paz.user',
            'email' => 'paz@example.com',
        ]);

        $this->actingAs($user)->put(route('admin.settings.profile.account'), [
            'settings_modal' => 'account',
            'username' => 'paz.updated',
            'email' => 'paz.updated@example.com',
        ])->assertRedirect(route('admin.settings.profile'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'paz.updated',
            'email' => 'paz.updated@example.com',
            'email_verified_at' => null,
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_upload_a_profile_photo_and_replace_the_previous_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/old.png', 'old photo');
        $user = User::factory()->create([
            'profile_photo_path' => 'profile-photos/old.png',
        ]);

        $this->actingAs($user)->put(route('admin.settings.profile.account'), [
            'settings_modal' => 'account',
            'username' => 'photo.user',
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect(route('admin.settings.profile'));

        $photoPath = $user->fresh()->profile_photo_path;
        $this->assertNotSame('profile-photos/old.png', $photoPath);
        Storage::disk('public')->assertExists($photoPath);
        Storage::disk('public')->assertMissing('profile-photos/old.png');
    }

    public function test_invalid_profile_photo_keeps_account_information_unchanged(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'username' => 'original.user',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($user)->put(route('admin.settings.profile.account'), [
            'settings_modal' => 'account',
            'username' => 'updated.user',
            'email' => 'updated@example.com',
            'profile_photo' => UploadedFile::fake()->image('large.png')->size(2049),
        ]);

        $response->assertSessionHasErrors('profile_photo');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'original.user',
            'email' => 'original@example.com',
        ]);
    }

    public function test_user_cannot_choose_a_username_already_used_by_another_account(): void
    {
        User::factory()->create(['username' => 'taken.user']);
        $user = User::factory()->create(['username' => 'current.user']);

        $this->actingAs($user)->put(route('admin.settings.profile.account'), [
            'settings_modal' => 'account',
            'username' => 'taken.user',
            'email' => $user->email,
        ])->assertSessionHasErrors('username');

        $this->assertSame('current.user', $user->fresh()->username);
    }

    public function test_guest_is_redirected_to_login_from_profile_settings(): void
    {
        $this->get(route('admin.settings.profile'))->assertRedirect('/login');
        $this->put(route('admin.settings.profile.account'))->assertRedirect('/login');
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
