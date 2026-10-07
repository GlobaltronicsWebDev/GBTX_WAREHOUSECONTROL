<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create([
            'email' => 'mark.estoesta@globaltronics.net',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status', __('passwords.sent'));
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/reset-password/sample-token?email=mark.estoesta@globaltronics.net');

        $response->assertStatus(200);
        $response->assertSee('SET NEW PASSWORD');
        $response->assertSee('mark.estoesta@globaltronics.net');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Event::fake([PasswordReset::class]);

        $user = User::factory()->create([
            'email' => 'mark.estoesta@globaltronics.net',
            'password' => Hash::make('OldPassword@123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword@2026',
            'password_confirmation' => 'NewSecurePassword@2026',
        ]);

        $response->assertSessionHas('status', __('passwords.reset'));
        $response->assertRedirect('/login');

        $this->assertTrue(Hash::check('NewSecurePassword@2026', $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'mark.estoesta@globaltronics.net',
            'password' => Hash::make('OldPassword@123'),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewSecurePassword@2026',
            'password_confirmation' => 'NewSecurePassword@2026',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('OldPassword@123', $user->fresh()->password));
    }
}
