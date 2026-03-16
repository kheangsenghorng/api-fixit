<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'        => $this->user_id,
            'business_name'  => $this->business_name,
            'address'        => $this->address,

            // ✅ NEW: location
            'lat'            => $this->lat !== null ? (float) $this->lat : null,
            'lng'            => $this->lng !== null ? (float) $this->lng : null,
            'map_url'        => $this->map_url,

            'images' => is_array($this->images)
            ? collect($this->images)->map(fn ($img) => [
                'url' => asset('storage/' . $img),
                'path' => $img,
            ])->toArray()
            : [],

            'logo' => $this->logo
                ? asset('storage/' . $this->logo)
                : null,

            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->final_status, // ✅ final status from documents
        ];
    }
}
