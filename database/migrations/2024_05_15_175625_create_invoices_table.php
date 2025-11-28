<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores');


            $table->unsignedBigInteger('sub_total')->comment('total including discount excluding tax');
            $table->unsignedBigInteger('discount_amount');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('payment_balance');
            $table->unsignedBigInteger('tax_amount');
            $table->string('status')
                ->comment('paid,pending_payment,part_payment,refunded')->default('pending_payment');
            $table->longText('notes')->nullable();
            $table->integer('tax_percentage');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
