<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Customer;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Invoice::factory()
            ->count(100)->hasInvoiceItems(10)->hasPayments(10)
            ->create([
                'store_id' => 1,
                'customer_id' => Customer::where('store_id', 1)->get()->random()->id
            ]);
        Invoice::factory()
            ->count(100)->hasInvoiceItems(10)->hasPayments(10)
            ->create();
    }
}
