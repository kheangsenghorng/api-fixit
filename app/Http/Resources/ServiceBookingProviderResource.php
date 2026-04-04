<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceBookingProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_booking_id' => $this->service_booking_id,
            'provider_id' => $this->provider_id,
            'assigned_by' => $this->assigned_by,
            'role' => $this->role,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'service_booking' => $this->whenLoaded('serviceBooking'),
            'provider' => $this->whenLoaded('provider'),
            'assigned_by_owner' => $this->whenLoaded('assignedByOwner'),
        ];
    }
}