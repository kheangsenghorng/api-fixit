<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $this->user,
            'phone' => 'nullable|unique:users,phone,' . $this->user,
            'password' => 'nullable|min:6|confirmed',
            'role' => 'in:customer,provider,admin,owner',
            'is_active' => 'boolean', // 👈 ADD THIS
        ];
    }
    
}
