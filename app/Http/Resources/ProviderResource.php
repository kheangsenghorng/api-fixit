<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'providerId' => $this->providerId,
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'category_id' => $this->category_id,
            'provider_type' => $this->provider_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user' => $this->whenLoaded('user'),
            'owner' => $this->whenLoaded('owner'),
            'category' => $this->whenLoaded('category'),
        ];
    }
}