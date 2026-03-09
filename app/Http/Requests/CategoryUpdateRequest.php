<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $categoryId = $this->route('category');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId)
            ],
            'category_group' => 'sometimes|required|in:service,mechanical',
            'status' => 'sometimes|in:active,inactive',
           'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048'
        ];
    }
}