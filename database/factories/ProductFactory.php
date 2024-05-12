<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Purchase;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ProductFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::random()->get()->id,
            'purchase_id' => Purchase::random()->get()->id,
            'quantity_purchased'=>fake()->numberBetween(20,100),
            'quantity_available'=>fake()->numberBetween(10,30),
            'unit_price'=>fake()->numberBetween(1000,3000)
        ];
    }
}
