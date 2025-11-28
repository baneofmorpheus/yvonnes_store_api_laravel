<?php

namespace Tests\Feature;

use App\Mail\SendLoginCodeEmail;
use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use App\Models\Store;
use App\Models\StoreUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;

class AuthenticationTest extends TestCase
{


    public function test_login_social(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $user->id]
        );

        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'owner',
            'is_default' => true,
        ]);


        // Create a mock Socialite User instance
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '123456789';
        $socialiteUser->name = 'Joe Jake';
        $socialiteUser->email = $user->email;
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';


        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('userFromToken')
            ->with('900000')
            ->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);


        $response = $this->postJson(
            '/api/v1/auth/login/social',
            ['provider' => 'google', 'access_token' => '900000']
        );


        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'user',
                'token',
                'user' => [
                    'name',
                    'email',
                    'is_active'
                ]
            ]]);
    }
    public function test_login_without_store(): void
    {
        Mail::fake();
        Queue::fake();


        // Create a mock Socialite User instance
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '123456789';
        $socialiteUser->name = 'Joe Jake';
        $socialiteUser->email = 'jake@gmail.com';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';


        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('userFromToken')
            ->with('900000')
            ->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);


        $response = $this->postJson(
            '/api/v1/auth/login/social',
            ['provider' => 'google', 'access_token' => '900000']
        );


        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'user',
                'token',
                'user' => [
                    'name',
                    'email',
                    'is_active'
                ]
            ]]);

        $user = User::where('email', 'jake@gmail.com')->firstOrFail();

        $store = Store::where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('store_users', [
            'user_id' => $user->id,
            'role' => 'owner',
            'store_id' => $store->id
        ]);
    }
}
