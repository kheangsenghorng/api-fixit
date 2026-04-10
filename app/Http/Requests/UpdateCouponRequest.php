<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unique_id' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('coupons', 'unique_id')->ignore($this->coupon),
            ],

            'owner_id' => ['nullable', 'exists:owners,id'],

            'discount_type' => ['sometimes', 'in:fixed,percent'],
            'discount_value' => ['sometimes', 'numeric', 'min:0.01'],

            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['sometimes', 'integer', 'min:1'],
            'max_uses_per_user' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:active,expired,disabled'],
        ];
    }
}