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
        Schema::create('invoice_item_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_item_id');

            $table->foreign('invoice_item_id')->references('id')->on('invoice_items');

            $table->unsignedBigInteger('purchase_item_id');

            $table->foreign('purchase_item_id')->references('id')->on('purchase_items');
            $table->integer('quantity_used_from_purchase');
            $table->integer('quantity_remaining_from_purchase');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_item_breakdowns');
    }
};
