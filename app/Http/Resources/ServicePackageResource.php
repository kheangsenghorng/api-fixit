<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicePackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'service_id' => $this->service_id,
            'service' => new ServiceResource($this->whenLoaded('service')),

            'title' => $this->title,
            'description' => $this->description,

            'min_area_m2' => $this->min_area_m2,
            'max_area_m2' => $this->max_area_m2,
            'floor_number' => $this->floor_number,
            'bedrooms' => $this->bedrooms,
            'duration_hours' => $this->duration_hours,
            'workers_count' => $this->workers_count,

            'price' => $this->price,
            'billing_type' => $this->billing_type,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}