<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\MarketplaceTestCase;

class PasswordRecoveryTest extends MarketplaceTestCase
{
    public function test_reset_link_is_single_use_and_revokes_tokens(): void
    {
        Notification::fake();
        $this->customer->createToken('Old', ['orders:read']);
        $this->post('/forgot-password', ['email' => $this->customer->email])->assertSessionHas('success');
        $token = null;
        Notification::assertSentTo($this->customer, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return str_contains($notification->toMail($this->customer)->actionUrl, '/reset-password/');
        });
        $payload = ['email' => $this->customer->email, 'token' => $token, 'password' => 'MyNewPassword123', 'password_confirmation' => 'MyNewPassword123'];
        $this->post('/reset-password', $payload)->assertRedirect('/login');
        $this->assertTrue(Hash::check('MyNewPassword123', $this->customer->fresh()->password));
        $this->assertSame(0, $this->customer->tokens()->count());
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
    }

    public function test_unknown_or_other_tenant_email_does_not_disclose_account_or_send_link(): void
    {
        Notification::fake();
        $this->post('/forgot-password', ['email' => 'unknown@example.test'])->assertSessionHas('success');
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'domain' => 'other.example.test']);
        $this->post('http://other.example.test/forgot-password', ['email' => $this->customer->email])->assertSessionHas('success');
        Notification::assertNothingSent();
    }
}
