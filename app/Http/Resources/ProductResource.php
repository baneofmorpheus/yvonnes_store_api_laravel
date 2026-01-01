<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'sku' => $this->sku,
            'store_id' => $this->store_id,
            'unit' => $this->unit,
            'quantity_remaining' => $this->quantity_remaining,
            'unit_price' => $this->unit_price,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            '_formatted' => $scout_data

        ];
    }
}
