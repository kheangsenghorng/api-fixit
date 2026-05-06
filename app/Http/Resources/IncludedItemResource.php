<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncludedItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'status' => $this->status,

            'sort_order' => $this->whenPivotLoaded('package_included_items', function () {
                return $this->pivot->sort_order;
            }),

            'created_at' => $this->created_at,
        ];
    }
}