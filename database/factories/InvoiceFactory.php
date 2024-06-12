<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Customer;
use App\Enum\PaymentStatus;

use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class InvoiceFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomNumber(7, true);
        $discount_amount = fake()->numberBetween(0, 4000);
        $taxes = fake()->numberBetween(0, 4000);
        $sub_total = $total - $taxes;
        $store_id = Store::get()->random()->id;
        return [
            'customer_id' => Customer::where('store_id', $store_id)->firstOrFail()->id,
            'store_id' => $store_id,
            'payment_balance' => 0,
            'status' => fake()->randomElement([
                PaymentStatus::PAID,
                PaymentStatus::PENDING_PAYMENT,
                PaymentStatus::PART_PAYMENT,
                PaymentStatus::REFUNDED,
            ]),
            'notes' => fake()->paragraphs(2, true),
            'total' => $total,
            'code' => Str::random(5),
            'tax_percentage' => round(($taxes / $total) * 100, 2),
            'tax_amount' => $taxes,
            'discount_amount' => $discount_amount,
            'sub_total' => $sub_total
        ];
    }
}
