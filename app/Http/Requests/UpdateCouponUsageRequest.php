<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon_id' => ['sometimes', 'exists:coupons,id'],
            'user_id' => ['sometimes', 'exists:users,id'],
            'times_used' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'coupon_id.exists' => 'Selected coupon does not exist.',

            'user_id.exists' => 'Selected user does not exist.',

            'times_used.integer' => 'Times used must be a number.',
            'times_used.min' => 'Times used must be at least 1.',
        ];
    }
}