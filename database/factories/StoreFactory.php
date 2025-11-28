<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class StoreFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'user_id' => User::factory(),
        ];
    }

    // public function configure(): static
    // {
    //     return $this->afterCreating(function (Store $store) {
    //         Customer::factory()->for($store)->count(100)->create();
    //         Product::factory()->for($store)->count(100)->create();
    //         // ...
    //     });
    // }
}
