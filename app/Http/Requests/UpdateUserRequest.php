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
        $userId = $this->route('user')->id;

        return [
            'name' => 'sometimes|string|max:255',

            'email' => 'nullable|email|unique:users,email,' . $userId,

            'phone' => [
                'nullable',
                'required_without:email',
                'string',
                'unique:users,phone,' . $userId,
            ],

            'password' => 'sometimes|min:6|confirmed',

            'role' => 'sometimes|in:customer,provider,admin,owner',

            'is_active' => 'sometimes|boolean',
        ];
    }
}
