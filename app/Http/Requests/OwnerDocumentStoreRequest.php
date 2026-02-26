<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OwnerDocument;

class OwnerDocumentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already protected
    }

    public function rules(): array
    {
        return [
            // Owner cannot control owner_id
            'document_type' => ['required', 'in:' . implode(',', OwnerDocument::DOCUMENT_TYPES)],
            'country' => ['required', 'string', 'size:2'],

            // File validation
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Please select document type.',
            'document_type.in' => 'Invalid document type.',

            'country.required' => 'Country is required.',
            'country.size' => 'Country must be 2-letter ISO code (e.g. KH).',

            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded item must be a valid file.',
            'file.mimes' => 'Only JPG, JPEG, PNG, and PDF files are allowed.',
            'file.max' => 'The file must not be larger than 5MB.',
        ];
    }
}