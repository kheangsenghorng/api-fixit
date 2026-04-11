<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'times_used',
    ];

    protected static function booted()
    {
        static::created(function ($usage) {
            $usage->coupon?->syncExpiredStatus();
            $usage->coupon?->syncUsageStatus();
        });

        static::updated(function ($usage) {
            $usage->coupon?->syncExpiredStatus();
            $usage->coupon?->syncUsageStatus();
        });

        static::deleted(function ($usage) {
            $coupon = $usage->coupon;
            $coupon?->refresh();
            $coupon?->syncExpiredStatus();
            $coupon?->syncUsageStatus();
        });
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}