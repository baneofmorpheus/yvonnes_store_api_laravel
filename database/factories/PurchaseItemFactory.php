<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PurchaseItemFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::get()->random()->id,
            'purchase_id' => Product::factory()->create(),
            'product_id' => Purchase::factory()->create(),
            'quantity_purchased' => fake()->numberBetween(30, 40),
            'quantity_available' => fake()->numberBetween(20, 40),
            'unit_price' => fake()->numberBetween(1000, 3000)
        ];
    }
}
