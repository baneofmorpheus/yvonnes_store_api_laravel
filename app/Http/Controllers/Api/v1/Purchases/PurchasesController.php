<?php

namespace App\Http\Controllers\Api\v1\Purchases;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Purchase\CreatePurchaseRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\PurchaseItem;
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
                PurchaseItem::create([
                    'purchase_id'        => $purchase->id,
                    'product_id'         => $item['product_id'],
                    'quantity_purchased' => $item['quantity_purchased'],
                    'quantity_available' => $item['quantity_purchased'],
                    'unit_price'         => $item['unit_price'],
                    'item_total'         => $item['quantity_purchased'] * $item['unit_price'],
                ]);
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

            $perPage =  request('per_page') ?? 20;

            $suppliers = Purchase::where('store_id', $store_id)
                ->get()->orderBy('created_at', 'desc')->paginate($perPage);


            $pagination = [
                'current_page' => $suppliers->currentPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'last_page' => $suppliers->lastPage(),
                'from' => $suppliers->firstItem(),
                'to' => $suppliers->lastItem(),
            ];






            return $this->successResponse('Purchases retrieved', 200, [
                'purchases' =>  $suppliers->items(),
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            Log::error("PurchasesController@getPurchases", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getPurchase(int $purchase_id)
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

            foreach ($purchase->items as $item) {

                if ($item->quantity_available !== $item->quantity_purchased) {
                    return $this->errorResponse('Cannot delete purchase with sold items', 400);
                }
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
