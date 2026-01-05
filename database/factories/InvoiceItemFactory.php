<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Product;
use App\Models\Invoice;
use App\Enum\PaymentStatus;

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

        $invoice = Invoice::get()->random();

        return [
            'invoice_id' => $invoice->id,
            'product_id' => Product::where('store_id', $invoice->store_id)->firstOrFail()->id,
            'quantity_purchased' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->numberBetween(100, 4000),
            'item_total' => fake()->numberBetween(1000, 400000),
        ];
    }
}
