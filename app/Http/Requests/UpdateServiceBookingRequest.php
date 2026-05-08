<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'service_id' => ['sometimes', 'exists:services,id'],
            'address_id' => ['sometimes', 'exists:user_addresses,id'],
            'booking_date' => ['sometimes', 'date'],
            'booking_hours' => ['sometimes', 'nullable', 'string', 'max:255'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'notes' => ['sometimes', 'nullable', 'string'],

            'booking_status' => [
                'sometimes',
                'in:pending,confirmed,in_progress,awaiting_customer_confirmation,completed,cancelled,disputed'
            ],

            'customer_status' => [
                'sometimes',
                'in:pending,completed,disputed'
            ],

            'customer_completed_at' => ['sometimes', 'nullable', 'date'],
            'auto_complete_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}