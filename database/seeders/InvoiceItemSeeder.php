<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceItem;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        InvoiceItem::factory()
            ->count(100)->hasinvoiceItemBreakdowns(10)
            ->create();
    }
}
