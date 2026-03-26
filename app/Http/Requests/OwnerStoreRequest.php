<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class OwnerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
        // Later you can restrict: return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) =>
                    $q->where('role', 'customer')
                ),
                Rule::unique('owners', 'user_id'),
            ],
    
            'business_name' => ['required', 'string', 'max:255'],
            'address'       => ['required', 'string'],
             // ✅ NEW location validation
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],

            'map_url' => ['nullable', 'string', 'max:500'],
    
            // Multiple images
            'images'        => ['nullable', 'array'],
            'images.*'      => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    
            // Logo
            'logo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
    
}
