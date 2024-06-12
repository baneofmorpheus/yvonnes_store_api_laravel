<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Store;
use App\Models\Product;
use App\Models\InvoiceItem;
use App\Enum\PaymentStatus;
use App\Models\PurchaseItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class InvoiceItemBreakdownFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoice_item =InvoiceItem::get()->random();
        $store_id = $invoice_item->invoice->store_id;
        return [
            'invoice_item_id' => $invoice_item->id,
            'purchase_item_id' => PurchaseItem::where('store_id',$store_id)->firstOrFail()->id,
            'quantity_used_from_purchase'=>fake()->numberBetween(10, 10),
            'quantity_remaining_from_purchase'=>fake()->numberBetween(10, 20),
        ];
    }
}
