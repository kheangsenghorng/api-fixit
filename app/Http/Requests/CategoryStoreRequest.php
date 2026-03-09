<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'category_group' => 'required|in:service,mechanical',
            'status' => 'nullable|in:active,inactive',
            'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048'
        ];
    }
}