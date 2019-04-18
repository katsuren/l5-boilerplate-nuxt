<?php

namespace Tests\Feature\Api\Auth;

use App\Entities\User;
use Auth;
use Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function testCanLogin()
    {
        $password = Str::random(10);
        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);
        $response = $this->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => $password,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }
}
