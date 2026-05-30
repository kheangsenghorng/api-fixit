<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'business_name',
        'address',
        'lat',
        'lng',
        'map_url',
        'images',
        'logo',
        'status',

        // Telegram fields
        'telegram_connect_code',
        'telegram_group_id',
        'telegram_group_name',
        'telegram_connected',
    ];

    protected $casts = [
        'images' => 'array',
        'lat' => 'float',
        'lng' => 'float',
        'telegram_connected' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('business_name', 'like', "%{$search}%");
        }

        return $query;
    }

    public function scopeFilterUser($query, $userId)
    {
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    public function scopeFilterTrashed($query, $trashed)
    {
        if ($trashed === 'only') {
            $query->onlyTrashed();
        } elseif ($trashed === 'with') {
            $query->withTrashed();
        }

        return $query;
    }

    public function generateMapUrl(): ?string
    {
        if ($this->lat !== null && $this->lng !== null) {
            return "https://www.google.com/maps?q={$this->lat},{$this->lng}";
        }

        return null;
    }

    protected static function booted()
    {
        static::saving(function (Owner $owner) {
            $owner->map_url = ($owner->lat !== null && $owner->lng !== null)
                ? $owner->generateMapUrl()
                : null;
        });
    }

    public function documents()
    {
        return $this->hasMany(OwnerDocument::class);
    }

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }

    public function latestDocument()
    {
        return $this->hasOne(OwnerDocument::class)->latestOfMany();
    }

    public function paymentSplits()
    {
        return $this->hasMany(PaymentSplit::class, 'owner_id');
    }

    public function getFinalStatusAttribute(): string
    {
        $docs = $this->documents;

        if ($docs->isEmpty()) {
            return 'pending';
        }

        if ($docs->contains('status', 'pending')) {
            return 'pending';
        }

        if ($docs->contains('status', 'rejected')) {
            return 'rejected';
        }

        if ($docs->every(fn ($d) => $d->status === 'approved')) {
            return 'approved';
        }

        return 'pending';
    }
}