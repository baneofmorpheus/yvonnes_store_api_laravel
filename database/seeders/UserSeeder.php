<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PurchaseItem;
use App\Models\InvoiceItem;
use App\Models\Purchase;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::factory()
            ->create(['name' => 'Yvonne Chux', 'email' => 'epicgenii18@gmail.com']);
        $store  = Store::factory()->create(['user_id' => $user->id, 'name' => "Yvonne's Store"]);


        StoreUser::factory()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'owner',
            'is_default' => true,
        ]);

        Product::factory()

            ->count(10)
            ->create(['store_id' => $store->id]);

        Purchase::factory()
            ->has(PurchaseItem::factory()->count(4), 'purchaseItems')
            ->count(100)
            ->create([
                'store_id' => $store->id,

            ]);

        // Product::factory()
        //     ->has(PurchaseItem::factory()->count(4), 'purchaseItems')

        //     ->count(200)
        //     ->create(['store_id' => $store->id]);

        Supplier::factory()
            ->count(200)
            ->create(['store_id' => $store->id]);

        Customer::factory()
            ->count(200)
            ->create(['store_id' => $store->id]);


        $staff = User::factory()
            ->count(50)
            ->create();





        Invoice::factory()
            ->has(InvoiceItem::factory()->count(4), 'invoiceItems')
            ->count(100)
            ->create([
                'store_id' => $store->id,

            ]);

        $staff->each(function ($user) use ($store) {
            StoreUser::factory()->create([
                'user_id' => $user->id,
                'store_id' => $store->id,
                'role' => 'staff',
                'is_default' => true,
            ]);
        });
    }
}
