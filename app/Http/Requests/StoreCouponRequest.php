<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unique_id' => ['required', 'string', 'max:255', 'unique:coupons,unique_id'],
            'owner_id' => ['nullable', 'exists:owners,id'],
            'discount_type' => ['required', 'in:fixed,percent'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],

            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'max_uses' => ['required', 'integer', 'min:1'],
            'max_uses_per_user' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,expired,disabled'],
        ];
    }
}