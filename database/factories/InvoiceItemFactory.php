<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Invoice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class InvoiceItemFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {


        return [
            'invoice_id' =>  Invoice::get()->random()->id,
            'product_id' => Product::get()->random()->id,
            'quantity_purchased' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->numberBetween(100, 4000),
            'item_total' => fake()->numberBetween(1000, 400000),
        ];
    }
}
