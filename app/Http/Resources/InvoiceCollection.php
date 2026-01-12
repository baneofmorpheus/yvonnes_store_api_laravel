<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\InvoiceResource;



class InvoiceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'items' => InvoiceResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page'     => $this->perPage(),
                'total'        => $this->total(),
                'last_page'    => $this->lastPage(),
            ]
        ];
    }
}
