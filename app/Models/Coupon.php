<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'unique_id',
        'owner_id',
        'discount_type',
        'discount_value',
        'expires_at',
        'max_uses',
        'max_uses_per_user',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'discount_value' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::retrieved(function ($coupon) {
            $coupon->syncExpiredStatus();
            $coupon->syncUsageStatus();
        });
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    public function totalTimesUsed(): int
    {
        return (int) $this->usages()->sum('times_used');
    }

    public function syncExpiredStatus(): void
    {
        if ($this->isExpired()) {
            if ($this->status !== 'expired') {
                $this->updateQuietly([
                    'status' => 'expired',
                ]);

                $this->status = 'expired';
            }
        }
    }

    public function syncUsageStatus(): void
    {
        if ($this->status === 'expired') {
            return;
        }

        $totalUsed = $this->totalTimesUsed();

        if ($this->max_uses !== null && $totalUsed >= $this->max_uses) {
            if ($this->status !== 'disabled') {
                $this->updateQuietly([
                    'status' => 'disabled',
                ]);

                $this->status = 'disabled';
            }
        } else {
            if ($this->status === 'disabled') {
                $this->updateQuietly([
                    'status' => 'active',
                ]);

                $this->status = 'active';
            }
        }
    }
}