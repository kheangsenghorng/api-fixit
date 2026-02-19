<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'business_name' => $this->business_name,
            'address' => $this->address,
            
            'images' => is_array($this->images)
                ? collect($this->images)->map(function ($image) {
                    return asset('storage/' . $image);
                })->toArray()
                : [],


            'logo' => $this->logo 
                ? asset('storage/' . $this->logo)
                : null,
             // 👇 Add this line here
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
