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
        'expires_at' => 'date',
        'discount_value' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}