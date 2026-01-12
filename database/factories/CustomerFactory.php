<?php

namespace Database\Factories;

use App\Models\Store;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class CustomerFactory extends Factory
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
            'name' => fake()->name(),
            'store_id' => Store::get()->random()->id,
            'address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),

        ];
    }
}
