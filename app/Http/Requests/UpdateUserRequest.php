<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users|required_without:phone',
            'phone' => 'sometimes|unique:users|required_without:email',
            'password' => 'sometimes|min:6|confirmed',
            'role' => 'in:customer,provider,admin,owner',
            'is_active' => 'boolean', // 👈 ADD THIS
        ];
    }
    
}
