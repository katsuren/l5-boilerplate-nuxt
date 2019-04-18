<?php

namespace Tests\Feature\Auth;

use App\Entities\User;
use App\Notifications\VerifyEmailNotification;
use Auth;
use Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function testCanResisterAndVerify()
    {
        Notification::fake();
        $pass = Str::random(10);
        $user = factory(User::class)->make(['password' => Hash::make($pass)]);
        $response = $this->json('POST', '/api/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $pass,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $response->assertOk()->assertJson(['ok']);
        $this->assertDatabaseHas('users', ['name' => $user->name, 'email' => $user->email]);

        $url = '';
        $user = User::where(['name' => $user->name, 'email' => $user->email])->first();
        Notification::assertSentTo(
            $user,
            VerifyEmailNotification::class,
            function ($notification, $channels, $notifiable) use ($user, &$url) {
                $url = $notification->toMail($user)->actionUrl;
                return $notifiable->routeNotificationFor('mail') === $user->email;
            }
        );

        $response = $this->get($url)->assertOk();
        $url = str_replace('/pages/email/verify', '/api/email/verify', $url);

        $response = $this->json('POST', $url, [
            'email' => $user->email,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $response->assertOk()->assertJsonStructure(['token']);

        $user->refresh();
        $this->assertNotEmpty($user->email_verified_at);
    }

    public function testCanResendVerifyEmail()
    {
        Notification::fake();
        $pass = Str::random(10);
        $user = factory(User::class)->make(['password' => Hash::make($pass)]);
        $this->json('POST', '/api/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $pass,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response = $this->json('POST', '/api/email/resend', [
            'email' => $user->email,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $user = User::where(['name' => $user->name, 'email' => $user->email])->first();
        Notification::assertSentTo(
            $user,
            VerifyEmailNotification::class,
            2
        );
    }
}
