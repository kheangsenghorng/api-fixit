<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            'label' => $this->label,
            'street_number' => $this->street_number,
            'house_number' => $this->house_number,

            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_url' => $this->map_url,

            'is_default' => $this->is_default,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}