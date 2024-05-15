<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Purchase::factory()
            ->count(100)->hasPurchaseItems(1,['store_id'=>1])
            ->create(['store_id'=>1]);
    }
}
