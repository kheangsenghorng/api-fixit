<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'package_id' => ['nullable', 'exists:service_packages,id'],
            'address_id' => ['required', 'exists:user_addresses,id'],
            'booking_date' => ['required', 'date'],
            'booking_hours' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],

            'booking_status' => [
                'nullable',
                'in:pending,confirmed,in_progress,awaiting_customer_confirmation,completed,cancelled,disputed'
            ],

            'customer_status' => [
                'nullable',
                'in:pending,completed,disputed'
            ],

            'customer_completed_at' => ['nullable', 'date'],
            'auto_complete_at' => ['nullable', 'date'],
        ];
    }
}