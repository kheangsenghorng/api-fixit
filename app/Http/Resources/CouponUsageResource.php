<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponUsageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coupon_id' => $this->coupon_id,
            'user_id' => $this->user_id,
            'times_used' => $this->times_used,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}