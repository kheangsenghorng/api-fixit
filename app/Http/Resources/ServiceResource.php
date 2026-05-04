<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'owner' => new OwnerResource($this->whenLoaded('owner')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'type' => new TypeResource($this->whenLoaded('type')),

            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,

            'images' => collect($this->images ?? [])
                ->map(fn ($img) => [
                    'url' => asset('storage/' . $img),
                    'path' => $img,
                ])
                ->values(),

            'created_at' => $this->created_at,
        ];
    }
}