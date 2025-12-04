<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\Supplier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class SupplierTest extends TestCase
{


    public function test_add_create_supplier(): void
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
            'role' => 'staff',
            'is_default' => true,
        ]);


        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->postJson(
            "/api/v1/suppliers",
            [
                'address' => '10 Joe road',
                'phone_number' => '08012345678',
                'name' => 'Joe king',
                'store_id' => $store->id
            ]
        );


        $response->dump();


        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'supplier' => [
                    'name',
                    'phone_number',
                    'address'
                ]
            ]]);



        $this->assertDatabaseHas('suppliers', [
            'address' => '10 Joe road',
            'phone_number' => '08012345678',
            'name' => 'Joe king',
            'store_id' => $store->id
        ]);
    }




    public function test_get_supplier(): void
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
        $supplier =    Supplier::factory()->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/suppliers/{$supplier->id}/single"
        );



        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'supplier' => [
                    'name',
                    'phone_number',
                    'address'
                ]
            ]]);
    }

    public function test_delete_supplier(): void
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
        $supplier =    Supplier::factory()->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->deleteJson(
            "/api/v1/suppliers/{$supplier->id}"
        );



        $response->assertStatus(200)
            ->assertJsonStructure([]);


        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
            'deleted_at' => null
        ]);
    }


    public function test_list_suppliers(): void
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
        Supplier::factory()->count(50)->create([
            'store_id' => $store->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/suppliers/$store->id"
        );




        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'suppliers' => [

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
