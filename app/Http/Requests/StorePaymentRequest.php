<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'owner_id' => ['required', 'exists:owners,id'],
            'service_booking_id' => ['required', 'exists:service_bookings,id'],
            'coupons_id' => ['nullable', 'exists:coupons,id'],
            'transaction_id' => ['nullable', 'string', 'unique:payments,transaction_id'],
            'original_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'final_amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::in(['bank_transfer', 'card', 'cash', 'bakong', 'khqr'])],
            'status' => ['nullable', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user_id does not exist.',
            'owner_id.exists' => 'The selected owner_id does not exist.',
            'service_booking_id.exists' => 'The selected service_booking_id does not exist.',
            'coupons_id.exists' => 'The selected coupons_id does not exist.',
        ];
    }
}