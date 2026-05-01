<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerDocument extends Model
{
    protected $fillable = [
        'owner_id',
        'document_type',
        'country',
        'file_path',
        'disk',
        'original_name',
        'mime_type',
        'size',
        'uploaded_at',
        'status',

        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
        'otp_verified_at',
        'otp_last_sent_at',

        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'otp_last_sent_at' => 'datetime',
        'reviewed_at' => 'datetime',

        'otp_attempts' => 'integer',
        'size' => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public const DOCUMENT_TYPES = [
        'passport',
        'id_card',
        'driver_license',
        'national_id',
        'birth_certificate',
    ];

    public const STATUSES = [
        'pending',
        'approved',
        'rejected',
    ];
}