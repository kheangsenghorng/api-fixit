<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerOwnerDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner' => new OwnerResource($this->whenLoaded('owner')),
            // document info
            'document_type' => $this->document_type,
            'country' => $this->country,

            // status + review (owner can see result)
            'status' => $this->status,
            'rejection_reason' => $this->status === 'rejected' ? $this->rejection_reason : null,
            'reviewed_at' => $this->reviewed_at,

            // file metadata (safe)
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,

            'uploaded_at' => $this->uploaded_at,
            'created_at' => $this->created_at,
        ];
    }
}