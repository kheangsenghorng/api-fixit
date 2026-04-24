<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'account_name' => 'required|string|max:255',
            'account_id' => 'required|string|max:255',
            'type_value' => 'required|string|max:255',
            'account_city' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
            'status' => 'required|integer|in:0,1',
        ];
    }
}