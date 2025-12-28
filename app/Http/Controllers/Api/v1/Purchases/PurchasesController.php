<?php

namespace App\Http\Controllers\Api\v1\Purchases;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Purchase\CreatePurchaseRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Http\Resources\PurchaseCollection;
use App\Http\Resources\PurchaseResource;


class PurchasesController extends Controller
{

    use ApiResponser;




    public function createPurchase(CreatePurchaseRequest $request)
    {

        try {
            $validated_data = $request->validated();

            DB::beginTransaction();

            $purchase =  Purchase::create([
                'store_id' => $validated_data['store_id'],
                'supplier_id' => $validated_data['supplier_id'],
                'total' => collect($validated_data['items'])
                    ->sum(fn($item) => $item['quantity_purchased'] * $item['unit_price'])
            ]);

            foreach ($validated_data['items'] as $item) {

                $product = Product::find($item['product_id']);
                PurchaseItem::create([
                    'purchase_id'        => $purchase->id,
                    'product_id'         => $item['product_id'],
                    'quantity_purchased' => $item['quantity_purchased'],
                    'quantity_available' => $item['quantity_purchased'],
                    'unit_price'         => $item['unit_price'],
                    'item_total'         => $item['quantity_purchased'] * $item['unit_price'],
                ]);

                ProductMovement::create([

                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'stock_before' => $product->quantity_remaining,
                    'quantity_change' => $item['quantity_purchased'],
                    'stock_after' => $product->quantity_remaining + $item['quantity_purchased'],
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->user()->id,

                ]);

                $product->update(['quanity_remaining' => $product->quantity_remaining + $item['quantity_purchased']]);
            }

            $purchase->refresh();
            DB::commit();
            return $this->successResponse('Purchase created ', 201, [
                'purchase' => new PurchaseResource($purchase)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("PurchasesController@createPurchase", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }

    public function getPurchases(int $store_id)
    {

        try {


            $user = auth()->user();

            if (!$user->storeBelongsToUser($store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $perPage = (int) request('per_page') ?? 20;




            $purchases = Purchase::where('store_id', $store_id)
                ->orderBy('created_at', 'desc')->paginate($perPage);









            return $this->successResponse('Purchases retrieved', 200, [
                'purchases' =>  new PurchaseCollection($purchases),

            ]);
        } catch (\Exception $e) {
            Log::error("PurchasesController@getPurchases", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getSinglePurchase(int $purchase_id)
    {

        try {

            $user = auth()->user();
            $purchase = Purchase::where('id', $purchase_id)->firstOrFail();

            if (!$user->storeBelongsToUser($purchase->store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }


            return $this->successResponse('Supplier retrieved', 200, [
                'purchase' =>  new PurchaseResource($purchase)

            ]);
        } catch (\Exception $e) {
            Log::error("PurchasesController@getPurchase", [
                "error" => $e->getMessage(),
                'payload' => request()->all()

            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function deletePurchase(int $purchase_id)
    {
        try {

            $user = auth()->user();
            $purchase = Purchase::where('id', $purchase_id)->firstOrFail();

            if (!$user->storeBelongsToUser($purchase->store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }


            foreach ($purchase->purchaseItems as $item) {
                $product = Product::find($item['product_id']);

                if ($item->quantity_available !== $item->quantity_purchased) {
                    return $this->errorResponse('Cannot delete purchase with sold items', 400);
                }

                ProductMovement::create([

                    'product_id' => $product->id,
                    'quantity_change' => $item['quantity_purchased'],

                    'type' => 'purchase_return',
                    'stock_before' => $product->quantity_remaining,
                    'stock_after' => $product->quantity_remaining - $item['quantity_purchased'],
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->user()->id,

                ]);


                $product->update(['quanity_remaining' => $product->quantity_remaining - $item['quantity_purchased']]);
            }

            $purchase->delete();


            return $this->successResponse('Purchase deleted', 200, []);
        } catch (\Exception $e) {

            Log::error("PurchasesController@deletePurchase", [
                "error" => $e->getMessage(),
                'payload' => request()->all()
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
