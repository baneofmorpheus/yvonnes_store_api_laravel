<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Purchase;
use Illuminate\Support\Str;

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
            'name' => fake()->company(),
            'store_id' => Store::get()->random()->id,
            'unit_price' => fake()->numberBetween(1000, 3000),
            'sku' => Str::random(7),
            'unit' => fake()->word(),
            'image_url' => fake()->imageUrl(),

        ];
    }
}
