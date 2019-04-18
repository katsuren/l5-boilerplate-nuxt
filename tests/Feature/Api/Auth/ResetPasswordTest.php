<?php

namespace Tests\Feature\Api\Auth;

use App\Entities\User;
use App\Notifications\ResetPasswordNotification;
use Auth;
use Hash;
use Tests\TestCase;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function testCanResetPassword()
    {
        Notification::fake();
        $user = factory(User::class)->create();
        $response = $this->json('POST', '/api/password/email', [
            'email' => $user->email,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $response->assertOk()->assertJson(['ok']);
        $token = '';
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function ($notification, $channels, $notifiable) use ($user, &$token) {
                $token = $notification->token;
                return $notifiable->routeNotificationFor('mail') === $user->email;
            }
        );

        $new = Str::random(10);
        $this->assertFalse(app('hash')->check($new, $user->password));
        $response = $this->json('POST', '/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => $new,
            'password_confirmation' => $new,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $response->assertOk()->assertJsonStructure(['token']);

        $user->refresh();
        $this->assertTrue(app('hash')->check($new, $user->password));
    }
}
