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

            'rating' => $this->rating,
            'total_jobs' => $this->total_jobs,
            'comment' => $this->comment,

            'provider_name' => $this->user?->name,
            'avatar' => $this->user?->avatar,
            'business_name' => $this->owner?->business_name,
            'specialty' => $this->category?->name,

            'user' => $this->whenLoaded('user'),
            'owner' => $this->whenLoaded('owner'),
            'category' => $this->whenLoaded('category'),
            'bookingProviders' => $this->whenLoaded('bookingProviders'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}