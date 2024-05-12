<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Purchase;
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
            'store_id' => Store::random()->get()->id,
            'sku'=>Str::random(7),
            'image_url'=>fake()->imageUrl(),
            'unit'=>fake()->numberBetween(1000,3000)
        ];
    }
}
