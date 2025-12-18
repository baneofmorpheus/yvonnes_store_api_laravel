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
use App\Models\Purchase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;

class InvoiceTest extends TestCase
{


    public function test_create_invoice(): void
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


    public function test_get_invoices(): void
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

        $supplier = Supplier::factory()->create(['store_id' => $store->id]);


        Product::factory()->count(100)
            ->create(['store_id' => $store->id]);


        Purchase::factory()
            ->count(100)
            ->withItems(5)
            ->create([
                'supplier_id' => $supplier->id,
                'store_id' => $store->id
            ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/purchases/$store->id",

        );


        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'purchases' => [
                    'items' => [
                        '*' => [
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

    public function test_get_single_invoice(): void
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

        $db = DB::connection();

        dump([
            'APP_ENV'           => env('APP_ENV'),
            'DB_CONNECTION'     => env('DB_CONNECTION'),
            'DB_DATABASE'       => env('DB_DATABASE'),
            'config.default'    => config('database.default'),
            'config.driver'     => $db->getDriverName(),
            'config.database'   => $db->getDatabaseName(),
            'is_in_memory'      => $db->getDriverName() === 'sqlite'
                && $db->getDatabaseName() === ':memory:',
        ]);

        $supplier = Supplier::factory()->create(['store_id' => $store->id]);


        Product::factory()->count(100)
            ->create(['store_id' => $store->id]);


        Purchase::factory()
            ->count(100)
            ->withItems(5)
            ->create([
                'supplier_id' => $supplier->id,
                'store_id' => $store->id
            ]);

        $purchase = Purchase::factory()
            ->withItems(5)
            ->create([
                'supplier_id' => 1,
                'store_id' => 1
            ]);




        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->getJson(
            "/api/v1/purchases/$purchase->id/single",

        );


        $response->assertStatus(200)
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
    }

    public function test_delete_invoice(): void
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

        $supplier = Supplier::factory()->create(['store_id' => $store->id]);


        Product::factory()->count(100)
            ->create(['store_id' => $store->id]);



        $purchase = Purchase::factory()
            ->withItems(5)
            ->create([
                'supplier_id' => $supplier->id,
                'store_id' => $store->id
            ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $user->token",
        ])->deleteJson(
            "/api/v1/purchases/$purchase->id",

        );



        $response->assertStatus(200)
            ->assertJsonStructure([]);


        $this->assertDatabaseMissing('purchases', [
            'id' => $purchase->id,
            'deleted_at' => null,
        ]);
    }
}
