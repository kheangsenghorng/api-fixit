<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'service_id' => $this->service_id,

            'street_number' => $this->street_number,
            'house_number' => $this->house_number,
            'booking_date' => $this->booking_date?->format('Y-m-d'),
            'booking_hours' => $this->booking_hours,

            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_url' => $this->map_url,

            'quantity' => $this->quantity,
            'notes' => $this->notes,

            'booking_status' => $this->booking_status,
            'customer_status' => $this->customer_status,
            'customer_completed_at' => $this->customer_completed_at,
            'auto_complete_at' => $this->auto_complete_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user' => $this->whenLoaded('user'),

            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->title,
                    'category_id' => $this->service->category_id,
                    'type_id' => $this->service->type_id,

                    'category' => $this->service->relationLoaded('category')
                        ? $this->service->category
                        : null,

                    'type' => $this->service->relationLoaded('type')
                        ? $this->service->type
                        : null,
                ];
            }),
            'payment' => $this->whenLoaded('payment', function () {
                return $this->payment->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'user_id' => $payment->user_id,
                        'owner_id' => $payment->owner_id,
                        'service_booking_id' => $payment->service_booking_id,
                        'coupons_id' => $payment->coupons_id,
                        'transaction_id' => $payment->transaction_id,
                        'original_amount' => $payment->original_amount,
                        'discount_amount' => $payment->discount_amount,
                        'final_amount' => $payment->final_amount,
                        'method' => $payment->method,
                        'status' => $payment->status,
                    ];
                });
            }),
        ];
    }
}