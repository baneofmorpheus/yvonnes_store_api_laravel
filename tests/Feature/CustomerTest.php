<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\Customer;

class CustomerTest extends TestCase
{


    public function test_add_create_customer(): void
    {


        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $user->id]
        );

        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'staff',
            'is_default' => true,
        ]);


        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/customers",
            [
                'address' => '10 Joe road',
                'phone_number' => '08012345678',
                'name' => 'Joe king',
                'store_id' => $store->id
            ]
        );




        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'customer' => [
                    'name',
                    'phone_number',
                    'address'
                ]
            ]]);



        $this->assertDatabaseHas('customers', [
            'address' => '10 Joe road',
            'phone_number' => '08012345678',
            'name' => 'Joe king',
            'store_id' => $store->id
        ]);
    }




    public function test_get_customer(): void
    {

        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $user->id]
        );

        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'staff',
            'is_default' => true,
        ]);
        $customer =    Customer::factory()->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/customers/{$customer->id}/single"
        );



        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'customer' => [
                    'name',
                    'phone_number',
                    'address'
                ]
            ]]);
    }

    public function test_update_customer(): void
    {

        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $user->id]
        );

        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'staff',
            'is_default' => true,
        ]);
        $customer =    Customer::factory()->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/customers/{$customer->id}",
            [
                'address' => '10 Joe roads',
                'phone_number' => '080123456783',
                'name' => 'Joe king3',
            ]
        );



        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'customer' => [
                    'name',
                    'phone_number',
                    'address'
                ]
            ]]);

        $this->assertDatabaseHas('customers', [
            'address' => '10 Joe roads',
            'phone_number' => '080123456783',
            'name' => 'Joe king3',
            'id' => $customer->id
        ]);
    }

    public function test_delete_customer(): void
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
        $customer =    Customer::factory()->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->deleteJson(
            "/api/v1/customers/{$customer->id}"
        );



        $response->assertStatus(200)
            ->assertJsonStructure([]);


        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
            'deleted_at' => null
        ]);
    }


    public function test_list_customers(): void
    {

        $user = User::factory()->create();
        $store = Store::factory()->create(
            ['user_id' => $user->id]
        );

        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'staff',
            'is_default' => true,
        ]);
        Customer::factory()->count(50)->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/customers/$store->id"
        );





        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'customers' => [

                    'items' => [
                        '*' => [
                            'name',
                            'phone_number',
                            'address'
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
}
