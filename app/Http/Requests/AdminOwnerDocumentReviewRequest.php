<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OwnerDocument;

class AdminOwnerDocumentReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:' . implode(',', OwnerDocument::STATUSES)],
            'rejection_reason' => ['nullable','string','max:500'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $status = $this->input('status');
            $reason = $this->input('rejection_reason');

            if ($status === 'rejected' && (!$reason || trim($reason) === '')) {
                $v->errors()->add('rejection_reason', 'Rejection reason is required when status is rejected.');
            }
        });
    }
}