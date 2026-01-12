<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Product;
use App\Models\Invoice;
use App\Enum\PaymentType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class InvoicePaymentFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::get()->random()->id,
            'notes' => fake()->paragraphs(2, true),
            'amount_paid' => fake()->numberBetween(1000, 400000),
            'payment_type' => fake()->randomElement([PaymentType::CASH, PaymentType::CHEQUE, PaymentType::TRANSFER, PaymentType::POS]),
        ];
    }
}
