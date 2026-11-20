<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_can_resend_verification_email_and_access_dashboard(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);
        $user = User::factory()->unverified()->create();
        $user->roles()->attach(Role::where('slug', 'it-admin')->first());

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();

        $this->post(route('verification.send'))
            ->assertRedirect(route('admin.settings.profile'))
            ->assertSessionHas('status', 'A verification email has been sent.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_valid_signed_link_verifies_the_authenticated_user(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $this->actingAs($user)->get($verificationUrl)
            ->assertRedirect(route('admin.settings.profile'))
            ->assertSessionHas('status', 'Your email address has been verified.');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_unsigned_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]))->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_guest_cannot_request_a_verification_email(): void
    {
        $this->post(route('verification.send'))->assertRedirect(route('login'));
    }
}
