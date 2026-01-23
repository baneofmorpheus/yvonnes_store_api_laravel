<?php

namespace App\Http\Controllers\Api\v1\Invoices;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Invoice\CreateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Services\UtilityService;
use Carbon\Carbon;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{

    use ApiResponser;




    public function createInvoice(int $store_id, CreateInvoiceRequest $request)
    {

        try {
            $validated_data = $request->validated();

            DB::beginTransaction();

            $sub_total = 0;
            $invoice =  Invoice::create([
                'store_id' => $store_id,
                'customer_id' => $validated_data['customer_id'],
                'discount_amount' => $validated_data['discount_amount'],
                'tax_percentage' => 15,
                'status' => 'pending_payment',
                'notes' => $validated_data['notes'],
                'code' => UtilityService::generateUniqueId(Invoice::class, 'code'),
                'tax_amount' => 0,

                'sub_total' => $sub_total,

                'total' => 0,
                'payment_balance' => 0,
            ]);

            foreach ($validated_data['items'] as $item) {



                $product = Product::findOrFail($item['product_id']);
                if ($product->quantity_remaining < $item['quantity_purchased']) {
                    return $this->errorResponse('Insufficient stock for product: ' . $product->name, 400, []);
                }


                $invoice_item = InvoiceItem::create([
                    'invoice_id'        => $invoice->id,
                    'product_id'         => $item['product_id'],
                    'quantity_purchased' => $item['quantity_purchased'],
                    'unit_price'         => $product->unit_price,
                    'item_total'         => $item['quantity_purchased'] * $product->unit_price,
                ]);

                $sub_total = $sub_total + $invoice_item->item_total;
                InvoiceService::updateSalePurchaseRecords($invoice->id, $product->id, $item['quantity_purchased']);
            }


            $sub_total =  max(
                0,
                $sub_total - $validated_data['discount_amount']
            );
            $tax_amount = ($sub_total * (15 / 100));

            $total = $sub_total + $tax_amount;


            $invoice->update([
                'sub_total' => $sub_total,
                'total' => $total,
                'tax_amount' => $tax_amount,
                'payment_balance' => $total,



            ]);
            $invoice->refresh();
            DB::commit();
            return $this->successResponse('Invoice created ', 201, [
                'purchase' => new InvoiceResource($invoice)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("InvoiceController@createInvoice", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }

    public function getInvoices(int $store_id)
    {

        try {


            $user = auth()->user();

            if (!$user->storeBelongsToUser($store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $perPage = (int) request('per_page') ?? 20;


            $invoices = Invoice::where('store_id', $store_id)
                ->when(request('today'), function ($q) {
                    $q->whereBetween('created_at', [
                        Carbon::today()->startOfDay(),
                        Carbon::today()->endOfDay(),
                    ]);
                })
                ->orderBy('created_at', 'desc')->paginate($perPage);







            return $this->successResponse('Invoices retrieved', 200, [
                'invoices' =>  new InvoiceCollection($invoices),
            ]);
        } catch (\Exception $e) {
            Log::error("InvoiceController@getInvoices", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function searchInvoice(int $store_id)
    {

        try {





            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }




            $invoices = Invoice::search(
                request('query') ?? '',
                function ($meilsearch, string $query, array $options) {
                    $options['attributesToHighlight'] =  ['customer_name'];
                    return $meilsearch->search($query, $options);
                }
            )->where('store_id', $store_id)

                ->orderBy('created_at', 'desc')->get();




            return $this->successResponse('Searched invoices retrieved', 200, [
                'invoices' =>  InvoiceResource::collection($invoices),
            ]);
        } catch (\Exception $e) {
            Log::error("InvoiceController@searchInvoice", ["error" => $e->getMessage(), 'query' =>    request('query')]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }



    public function getInvoice(int $invoice_id)
    {

        try {

            $invoice = Invoice::where('id', $invoice_id)->firstOrFail();

            $user = auth()->user();

            if (!$user->storeBelongsToUser($invoice->store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }


            return $this->successResponse('Invoice retrieved', 200, [
                'invoice' =>  new InvoiceResource($invoice)

            ]);
        } catch (\Exception $e) {
            Log::error("InvoiceController@getInvoice", [
                "error" => $e->getMessage(),
                'payload' => request()->all()
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function deleteInvoice(int $invoice_id)
    {
        try {

            $user = auth()->user();

            $invoice = Invoice::where('id', $invoice_id)
                ->firstOrFail();

            if (!$user->storeBelongsToUser($invoice->store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }



            InvoiceService::updateSaleReturnPurchaseRecords($invoice->id);




            $invoice->delete();


            return $this->successResponse('Invoice deleted', 200, []);
        } catch (\Exception $e) {

            Log::error("InvoiceController@deleteInvoice", [
                "error" => $e->getMessage(),
                'payload' => request()->all()
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
