<?php

namespace Tests\Feature\Api\Auth;

use App\Entities\User;
use App\Notifications\VerifyUpdateEmailNotification;
use Auth;
use Hash;
use JWTAuth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function testCanGetAccount()
    {
        $user = factory(User::class)->create();
        $response = $this->json('GET', '/api/account', [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
        ]);
        $response->assertOk()->assertJsonStructure(['user']);
        $json = json_decode($response->content());
        $this->assertEquals($json->user->id, $user->id);
    }

    public function testCanUpdateAccount()
    {
        Notification::fake();
        $user = factory(User::class)->create();
        $new = Str::random(10);
        $email = $new . $user->email;

        $this->assertFalse(app('hash')->check($new, $user->password));
        $response = $this->json('PUT', '/api/account', [
            'user' => [
                'name' => $new,
                'password' => $new,
                'email' => $email,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
        ]);
        $response->assertOk()->assertJson([
            'result' => 'ok',
            'message' => [
                'sent email to verify',
            ],
        ]);
        $user->refresh();
        $this->assertEquals($new, $user->name);
        $this->assertNotEquals($email, $user->email);
        $this->assertTrue(app('hash')->check($new, $user->password));

        $url = '';
        Notification::assertSentTo(
            new AnonymousNotifiable,
            VerifyUpdateEmailNotification::class,
            function ($notification, $channels, $notifiable) use ($email, &$url) {
                $url = $notification->toMail($notifiable)->actionUrl;
                return $notifiable->routes['mail'] === $email;
            }
        );
        $url = str_replace('/pages/account/verify', '/api/account/verify', $url);

        $response = $this->json('PUT', $url, [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
        ]);
        $response->assertOk()->assertJson(['result' => 'ok']);
        $user->refresh();
        $this->assertEquals($email, $user->email);
    }

    public function testCannotUpdateInvalidPassword()
    {
        $user = factory(User::class)->create();
        $new = Str::random(7);

        $this->assertFalse(app('hash')->check($new, $user->password));
        $response = $this->json('PUT', '/api/account', [
            'user' => [
                'password' => $new,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
        ]);
        $response->assertOk()->assertJson([
            'result' => 'error',
        ]);
        $user->refresh();
        $this->assertFalse(app('hash')->check($new, $user->password));
    }
}
