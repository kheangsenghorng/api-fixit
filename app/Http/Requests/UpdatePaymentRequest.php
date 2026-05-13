<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $payment = $this->route('payment');

        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'owner_id' => ['sometimes', 'exists:owners,id'],
            'service_booking_id' => ['sometimes', 'exists:service_bookings,id'],
            'coupons_id' => ['nullable', 'exists:coupons,id'],

            'transaction_id' => [
                'nullable',
                'string',
                Rule::unique('payments', 'transaction_id')
                    ->ignore($payment->id),
            ],

            'original_amount' => ['sometimes', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'final_amount' => ['sometimes', 'numeric', 'min:0'],
            'method' => [
                'sometimes',
                Rule::in(['bank_transfer', 'card', 'cash', 'bakong', 'khqr', 'wallet']),
            ],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'paid', 'failed', 'refunded']),
            ],
        ];
    }
}