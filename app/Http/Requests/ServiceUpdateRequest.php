<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'owner_id' => ['nullable','exists:owners,id'],
            'category_id' => ['sometimes','exists:categories,id'],
            'type_id' => ['sometimes','exists:types,id'],

            'title' => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],

            'status' => ['in:draft,active,paused'],

            'base_price' => ['sometimes','numeric','min:0'],
            'duration' => ['sometimes','integer','min:1'],

            'images' => ['nullable','array','max:4'],
            'images.*' => ['image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }
}