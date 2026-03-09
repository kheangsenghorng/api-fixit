<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OwnerDocument;

class OwnerDocumentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'in:' . implode(',', OwnerDocument::DOCUMENT_TYPES)],
            'country' => ['sometimes', 'string', 'size:2'],

            // ❌ remove status (only admin can approve/reject)
            // ❌ keep file optional
            'file' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}