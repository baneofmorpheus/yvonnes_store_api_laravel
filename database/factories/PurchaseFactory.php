<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Purchase;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PurchaseFactory extends Factory
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
            'supplier_id' => Store::get()->random()->id,
            'total'=>fake()->randomNumber(6,true)
        ];
    }
}
