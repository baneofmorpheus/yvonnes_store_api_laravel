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

        $quantity = fake()->numberBetween(30, 40);
        return [
            'purchase_id' => Purchase::factory()->create(),
            'product_id' => Product::factory()->create(),
            'quantity_purchased' => $quantity,
            'quantity_available' => $quantity,
            'unit_price' => fake()->numberBetween(1000, 3000),
            'item_total' => fake()->numberBetween(2000, 400000)
        ];
    }
}
