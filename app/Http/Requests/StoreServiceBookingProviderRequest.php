<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceBookingProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_booking_id' => ['required', 'exists:service_bookings,id'],
            'provider_id' => ['required', 'exists:providers,providerId'],
            'assigned_by' => ['nullable', 'exists:owners,id'],
            'role' => ['nullable', 'in:main,helper'],
            'status' => ['nullable', 'in:assigned,accepted,on_the_way,arrived,working,completed,declined'],
            'assigned_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}