<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
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
            'category_id' => ['required', 'exists:categories,id'],
            'provider_type' => ['required', 'in:staff,freelancer'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}