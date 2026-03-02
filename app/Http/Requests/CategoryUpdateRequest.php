<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'category_group' => 'sometimes|required|in:service,mechanical',
            'status' => 'sometimes|in:active,inactive',
            'icon' => 'nullable|string'
        ];
    }
}