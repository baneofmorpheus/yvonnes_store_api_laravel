<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use App\Models\Store;
use App\Models\StoreUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;

class StoreTest extends TestCase
{


    public function test_add_user_to_store(): void
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


        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/stores/$store->id/add-user",
            ['email' => 'joe@gmail.com', 'name' => 'Joe king']
        );


        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'user',
                'user' => [
                    'name',
                    'email',
                    'is_active'
                ]
            ]]);

        $user = User::where('email', 'joe@gmail.com')->firstOrFail();


        $this->assertDatabaseHas('store_users', [
            'user_id' => $user->id,
            'role' => 'staff',
            'store_id' => $store->id
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'joe@gmail.com',
            'name' => 'Joe king'
        ]);
    }



    public function test_update_store(): void
    {
        Mail::fake();
        Queue::fake();

        $owner = User::factory()->create();
        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $owner->id]
        );


        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'staff',
            'is_default' => true,
        ]);
        StoreUser::factory()->create([
            'user_id' => $owner->id,
            'store_id' => $store->id,
            'role' => 'owner',
            'is_default' => true,
        ]);


        $response = $this->withHeaders([
            'Authorization' => "Bearer $owner->token",
        ])->postJson(
            "/api/v1/stores/$store->id",

            ['name' => 'Store King']
        );


        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['store' => [
                'id',
                'name',
            ]]]);



        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' =>  'Store King',
        ]);
    }

    public function test_remove_user_from_store(): void
    {
        Mail::fake();
        Queue::fake();

        $owner = User::factory()->create();
        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $owner->id]
        );


        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'staff',
            'is_default' => true,
        ]);
        StoreUser::factory()->create([
            'user_id' => $owner->id,
            'store_id' => $store->id,
            'role' => 'owner',
            'is_default' => true,
        ]);
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


        $response = $this->withHeaders([
            'Authorization' => "Bearer $owner->token",
        ])->postJson(
            "/api/v1/stores/$store->id/remove-user",

            ['email' => $user->email]
        );


        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);


        $this->assertDatabaseMissing('store_users', [
            'user_id' => $user->id,
            'store_id' => $store->id,
        ]);
    }
}
