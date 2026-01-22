<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\InvoiceItemResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\InvoicePaymentResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


        $scout_data = $this->scoutMetadata();
        if (isset($scout_data) && isset($scout_data['_formatted'])) {
            $scout_data = $scout_data['_formatted'];
        }




        return [

            'id' => $this->id,
            'store_id' => $this->store_id,
            'customer' => new CustomerResource($this->customer),
            'sub_total' => $this->sub_total,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'tax_percentage' => $this->tax_percentage,
            'payment_balance' => $this->payment_balance,
            'total' => $this->total,
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => InvoiceItemResource::collection($this->invoiceItems),
            'payments' =>  $this->payments ? InvoicePaymentResource::collection($this->payments) : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            '_formatted' => $scout_data

        ];
    }
}
