<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\StoreUser;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Illuminate\Support\Facades\DB;

class PurchaseTest extends TestCase
{


    public function test_create_purchases(): void
    {


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

        $supplier = Supplier::factory()->create();


        $products =   Product::factory()->count(10)
            ->create(['store_id' => $store->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/purchases/$store->id",
            [
                'store_id' => $store->id,
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $products[0]->id,
                        'quantity_purchased' => 20,
                        'unit_price' => 300
                    ],
                    [
                        'product_id' => $products[2]->id,
                        'quantity_purchased' => 21,
                        'unit_price' => 300
                    ],
                    [
                        'product_id' => $products[3]->id,
                        'quantity_purchased' => 25,
                        'unit_price' => 400
                    ],
                ]
            ]
        );


        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'purchase' => [
                    'id',
                    'store_id',
                    'supplier' => ['name'],
                    'total',
                    'items' => [
                        '*' => [
                            'id',
                            'quantity_purchased',
                            'quantity_available',
                            'unit_price',
                            'product' => [
                                'name'
                            ],
                        ]
                    ]
                ]
            ]]);




        $this->assertEquals(
            3,
            DB::table('purchase_items')->count()
        );

        $this->assertDatabaseHas('purchases', [
            'store_id' => $store->id,
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
