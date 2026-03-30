<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'owner_id' => ['sometimes', 'exists:owners,id'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'provider_type' => ['sometimes', 'in:staff,freelancer'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}