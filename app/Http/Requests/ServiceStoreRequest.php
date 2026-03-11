<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'owner_id' => ['nullable','exists:owners,id'],
            'category_id' => ['required','exists:categories,id'],
            'type_id' => ['required','exists:types,id'],

            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],

            'status' => ['in:draft,active,paused'],

            'base_price' => ['required','numeric','min:0'],
            'duration' => ['required','integer','min:1'],

            'images' => ['nullable','array','max:4'],
            'images.*' => ['image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }
}