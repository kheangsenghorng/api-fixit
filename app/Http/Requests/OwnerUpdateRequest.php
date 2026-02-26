<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'sometimes',
                'exists:users,id',
                Rule::unique('owners', 'user_id')->ignore($this->route('owner')),
            ],

            'business_name' => ['sometimes', 'string', 'max:255'],
            'address'       => ['sometimes', 'string'],

            // ✅ NEW: location fields
            'lat'     => ['sometimes', 'numeric', 'between:-90,90'],
            'lng'     => ['sometimes', 'numeric', 'between:-180,180'],
            'map_url' => ['sometimes', 'string', 'max:500'],

            // Images
            'images'   => ['sometimes', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Logo
            'logo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
