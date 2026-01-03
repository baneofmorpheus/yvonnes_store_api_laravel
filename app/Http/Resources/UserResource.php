<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            '_formatted' => $scout_data

        ];
    }
}
