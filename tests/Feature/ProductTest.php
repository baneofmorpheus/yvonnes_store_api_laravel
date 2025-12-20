<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\StoreUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class ProductTest extends TestCase
{


    public function test_create_product(): void
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




        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/products",
            [

                'unit' => 'bags',
                'name' => 'Basket',
                'store_id' => $store->id,
                'unit_price' => 1500,

            ]
        );



        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'product',
                'product' => [
                    'id',
                    'name',
                    'sku',
                    'unit',
                    'unit_price',
                    'quantity_remaining',
                    'image_url',
                    'created_at',
                    'updated_at'
                ],

            ]]);



        $this->assertDatabaseHas('products', [
            'unit' => 'bags',
            'name' => 'Basket',
            'store_id' => $store->id,
            'unit_price' => 1500,
        ]);
    }


    public function test_update_product(): void
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


        $product = Product::factory()->create([
            'store_id' => $store->id,
        ]);



        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/products/$product->id",
            [

                'unit' => 'bags',
                'name' => 'Basket',
                'unit_price' => 1500,

            ]
        );



        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'product',
                'product' => [
                    'id',
                    'name',
                    'sku',
                    'unit',
                    'unit_price',
                    'quantity_remaining',
                    'image_url',
                    'created_at',
                    'updated_at'
                ],

            ]]);



        $this->assertDatabaseHas('products', [
            'unit' => 'bags',
            'name' => 'Basket',
            'store_id' => $store->id,
            'unit_price' => 1500,
        ]);
    }



    public function test_list_products(): void
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


        Product::factory()->count(50)->create([
            'store_id' => $store->id
        ]);


        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/products/$store->id",
            []
        );



        $response->assertStatus(200)
            ->assertJsonStructure(['data' =>  [
                'products' => [

                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'unit',
                            'unit_price',
                            'quantity_remaining',
                            'image_url',
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]
            ]]);
    }

    public function test_delete_product(): void
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


        $product = Product::factory()->create([
            'store_id' => $store->id,
        ]);



        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->deleteJson(
            "/api/v1/products/$product->id",
            []
        );



        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);



        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'deleted_at' => null,

        ]);
    }
}
