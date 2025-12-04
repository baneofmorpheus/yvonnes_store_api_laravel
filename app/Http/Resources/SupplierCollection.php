<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\SupplierResource;



class SupplierCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'items' => SupplierResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page'     => $this->perPage(),
                'total'        => $this->total(),
                'last_page'    => $this->lastPage(),
            ]
        ];
    }
}
