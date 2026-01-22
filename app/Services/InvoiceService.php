<?php

namespace App\Services;


use App\Models\StoreUser;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\PurchaseItem;
use App\Models\Store;
use Exception;

class InvoiceService
{
    public static function updateSalePurchaseRecords(int $invoice_id, int $product_id, int $quantity)
    {

        $product = Product::findOrFail($product_id);


        $quantity_deducted = 0;

        while ($quantity_deducted < $quantity) {

            $purchase_item = PurchaseItem::where('product_id', $product_id)
                ->where('quantity_available', '>', 0)
                ->oldest()->first();

            if ($purchase_item->quantity_available >= $quantity) {

                $purchase_item->quantity_available = $purchase_item->quantity_available - $quantity;

                $quantity_deducted = $quantity;
            } else {

                $quantity_deducted = $quantity_deducted + $purchase_item->quantity_available;
                $purchase_item->quantity_available = 0;
            }
            $purchase_item->save();

            ProductMovement::create([

                'product_id' => $product->id,
                'type' => 'sale',
                'stock_before' => $product->quantity_remaining,
                'quantity_change' => $quantity,

                'stock_after' => $product->quantity_remaining - $quantity,
                'invoice_id' => $invoice_id,
                'purchase_item_id' => $purchase_item->id,
                'user_id' => auth()->user()->id,

            ]);

            $product->quantity_remaining = $product->quantity_remaining - $quantity;

            $product->save();

            $product->refresh();
        }
    }


    public static function updateSaleReturnPurchaseRecords(int $invoice_id)
    {




        $product_movements = ProductMovement::where('invoice_id', $invoice_id)
            ->where('type', 'sale')->get();

        foreach ($product_movements as $item) {

            $purchase_item = PurchaseItem::find($item->purchase_item_id);

            $product = Product::findOrFail($purchase_item->product_id);


            $purchase_item->quantity_available = $purchase_item->quantity_available + $item->quantity_change;

            $purchase_item->save();

            ProductMovement::create([

                'product_id' => $purchase_item->product_id,
                'type' => 'sale_return',
                'quantity_change' => $item->quantity_change,
                'stock_before' => $product->quantity_remaining,
                'stock_after' => $product->quantity_remaining + $item->quantity_change,
                'invoice_id' => $invoice_id,
                'user_id' => auth()->user()->id,

            ]);

            $product->update(['quantity_remaining' => $product->quantity_remaining + $item->quantity_change]);
        }
    }
}
