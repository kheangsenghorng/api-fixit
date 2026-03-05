<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => new CategoryResource(
                $this->whenLoaded('category')
            ),
    
            'name' => $this->name,
            'icon' => $this->icon ? asset('storage/'.$this->icon) : null,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}