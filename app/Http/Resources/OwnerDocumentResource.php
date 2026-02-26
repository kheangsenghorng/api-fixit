<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->role === 'admin';

        return [
            'id' => $this->id,

            // Owner info
            'owner' => new OwnerResource($this->whenLoaded('owner')),
            
            // Document info
            'document_type' => $this->document_type,
            'country' => $this->country,

            // Metadata
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,

            // Status
            'status' => $this->status,

            // Review info
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at,
            'rejection_reason' => $this->rejection_reason,

            // Timestamps
            'uploaded_at' => $this->uploaded_at,
            'created_at' => $this->created_at,

            /*
            |--------------------------------------------------------------------------
            | Admin-only secure fields
            |--------------------------------------------------------------------------
            */
            'otp_verified' => $isAdmin ? (bool) $this->otp_verified_at : null,

            // Never expose encrypted path
            // 'file_path' => HIDDEN
            // 'otp_hash' => HIDDEN
            // 'disk' => HIDDEN
        ];
    }
}