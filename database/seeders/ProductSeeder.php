<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Product::factory()
            ->count(100)
            ->create(['store_id'=>1]);
        Product::factory()
            ->count(100)
            ->create();
    }
}
