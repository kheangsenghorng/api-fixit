<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'account_name' => 'sometimes|required|string|max:255',
            'account_id' => 'sometimes|required|string|max:255',
            'type_value' => 'sometimes|required|string|max:255',
            'account_city' => 'nullable|string|max:255',
            'currency' => 'sometimes|required|string|max:10',
            'status' => 'required|integer|in:0,1',
        ];
    }
}