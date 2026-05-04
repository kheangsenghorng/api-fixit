<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServicePackageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['sometimes', 'exists:services,id'],

            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'min_area_m2' => ['nullable', 'numeric', 'min:0'],
            'max_area_m2' => ['nullable', 'numeric', 'min:0', 'gte:min_area_m2'],

            'floor_number' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],

            'duration_hours' => ['nullable', 'numeric', 'min:0'],
            'workers_count' => ['nullable', 'integer', 'min:1'],

            'price' => ['sometimes', 'numeric', 'min:0'],

            'billing_type' => ['sometimes', 'in:one_time,weekly,monthly'],
            'status' => ['sometimes', 'in:draft,active,paused'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists' => 'Selected service does not exist.',

            'title.max' => 'Package title must not be greater than 255 characters.',

            'max_area_m2.gte' => 'Maximum area must be greater than or equal to minimum area.',

            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least 0.',

            'billing_type.in' => 'Billing type must be one_time, weekly, or monthly.',
            'status.in' => 'Status must be draft, active, or paused.',
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id' => 'service',
            'min_area_m2' => 'minimum area',
            'max_area_m2' => 'maximum area',
            'floor_number' => 'floor number',
            'duration_hours' => 'duration hours',
            'workers_count' => 'workers count',
            'billing_type' => 'billing type',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}