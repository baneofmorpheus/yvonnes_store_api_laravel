<?php

namespace App\Http\Controllers\Api\v1\Invoices;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Invoice\CreateInvoicePaymentRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoicePaymentResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoicePayment;

class InvoicePaymentController extends Controller
{

    use ApiResponser;




    public function createPayment(int $invoice_id, CreateInvoicePaymentRequest $request)
    {

        $validated_data = $request->validated();

        $invoice = Invoice::findOrFail($invoice_id);

        $user = auth()->user();

        if (!$user->storeBelongsToUser($invoice->store_id)) {
            return $this->errorResponse('You dont have access to this invoice', 403);
        }
        if ($invoice == 'paid') {
            return $this->errorResponse('Invoice already paid', 400);
        }

        $total_payment = InvoicePayment::where('invoice_id', $invoice->id)
            ->sum('amount');
        $expected_total = $invoice->total - $total_payment;


        if ($validated_data['amount'] > $expected_total) {
            return $this->errorResponse('Payment amount exceeds remaining balance', 400);
        }


        try {


            DB::beginTransaction();


            $payment = InvoicePayment::create([...$validated_data, 'invoice_id' => $invoice->id]);


            $invoice_status = 'part_payment';


            if ($validated_data['amount'] == $expected_total) {
                $invoice_status = 'paid';
            }



            $total_payment = InvoicePayment::where('invoice_id', $invoice->id)
                ->sum('amount');

            $invoice->update([
                'status' => $invoice_status,
                'payment_balance' => $invoice->total - $total_payment
            ]);
            $invoice->refresh();
            DB::commit();
            return $this->successResponse('Invoice payment successfull ', 201, [
                'payment' => new InvoicePaymentResource($payment)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("InvoicePaymentController@createPayment", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }

    public function getPayments(int $invoice_id)
    {

        try {


            $user = auth()->user();


            $invoice = Invoice::findOrFail($invoice_id);

            $user = auth()->user();

            if (!$user->storeBelongsToUser($invoice->store_id)) {
                return $this->errorResponse('You dont have access to this stores invoices', 403);
            }

            $payments = InvoicePayment::where('invoice_id', $invoice->id)
                ->orderBy('created_at', 'desc')->get();







            return $this->successResponse('Invoices retrieved', 200, [
                'invoices' =>  InvoiceResource::collection($payments),
            ]);
        } catch (\Exception $e) {
            Log::error("InvoiceController@getPayments", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }





    public function deletePayment(int $payment_id)
    {
        try {

            $invoice_payment = InvoicePayment::where('id', $payment_id)
                ->firstOrFail();

            $user = auth()->user();

            if (!$user->storeBelongsToUser($invoice_payment->invoice->store_id)) {
                return $this->errorResponse('Unauthorized', 403);
            }


            $invoice = $invoice_payment->invoice;

            $invoice_status = 'part_payment';
            $payment_balance = $invoice->payment_balance + $invoice_payment->amount;

            if ($invoice->total == $payment_balance) {
                $invoice_status = 'pending_payment';
            }

            $invoice->update([
                'status' => $invoice_status,
                'payment_balance' => $payment_balance
            ]);

            InvoicePayment::where('id', $payment_id)
                ->where('invoice_id', $invoice->id)->delete();


            return $this->successResponse('Invoice Payment deleted', 200, []);
        } catch (\Exception $e) {

            Log::error("InvoicePaymentController@deletePayment", [
                "error" => $e->getMessage(),
                'payload' => request()->all()
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
