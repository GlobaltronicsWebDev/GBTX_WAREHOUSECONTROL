<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'paz.user',
            'password' => 'UserPass123!',
        ]);

        $this->post(route('login'), [
            'username' => 'paz.user',
            'password' => 'UserPass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_still_login_with_email_address(): void
    {
        $user = User::factory()->create([
            'email' => 'paz@example.com',
            'password' => 'UserPass123!',
        ]);

        $this->post(route('login'), [
            'username' => 'paz@example.com',
            'password' => 'UserPass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
