<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,

            'sort_order' => $this->whenPivotLoaded('package_task_groups', function () {
                return $this->pivot->sort_order;
            }),

            'task_items' => TaskItemResource::collection(
                $this->whenLoaded('taskItems')
            ),

            'created_at' => $this->created_at,
        ];
    }
}