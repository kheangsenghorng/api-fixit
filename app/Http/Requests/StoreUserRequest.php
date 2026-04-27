<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',

            // Email OR phone
            'email' => 'nullable|email|unique:users,email|required_without:phone',
            'phone' => 'nullable|string|unique:users,phone|required_without:email',

            // Password is generated in controller, so not required from request
            'password' => 'nullable|min:6|confirmed',

            'role' => 'nullable|in:customer,provider,admin,owner',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email or phone is required.',
            'phone.required_without' => 'Email or phone is required.',
        ];
    }
}