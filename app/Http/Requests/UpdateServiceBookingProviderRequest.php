<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceBookingProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_booking_id' => ['sometimes', 'exists:service_bookings,id'],
            'provider_id' => ['sometimes', 'exists:providers,id'],
            'assigned_by' => ['sometimes', 'nullable', 'exists:owners,id'],
            'role' => ['sometimes', 'in:main,helper'],
            'status' => ['sometimes', 'in:assigned,accepted,on_the_way,arrived,working,completed,declined'],
            'assigned_at' => ['sometimes', 'nullable', 'date'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}