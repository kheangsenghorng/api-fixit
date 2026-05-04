<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['nullable', 'exists:owners,id'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'type_id' => ['sometimes', 'exists:types,id'],

            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'status' => ['sometimes', 'in:draft,active,paused'],

            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.array' => 'Images must be sent as a list.',
            'images.max' => 'You can upload up to 10 images only.',

            'images.*.file' => 'Each image must be a valid file.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPG, JPEG, PNG, or WEBP.',
            'images.*.max' => 'Each image must not be larger than 5 MB.',
            'images.*.uploaded' => 'One of the images failed to upload. Please try again.',
        ];
    }

    public function attributes(): array
    {
        return [
            'owner_id' => 'owner',
            'category_id' => 'category',
            'type_id' => 'type',
            'images' => 'images',
            'images.*' => 'image',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();

        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            if (preg_match('/^images\.(\d+)$/', $field, $matches)) {
                $index = (int) $matches[1] + 1;

                $formattedErrors["images[$matches[1]]"] = array_map(
                    fn ($message) => str_replace('image', "image {$index}", $message),
                    $messages
                );
            } else {
                $formattedErrors[$field] = $messages;
            }
        }

        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $formattedErrors,
        ], 422));
    }
}