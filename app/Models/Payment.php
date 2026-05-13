<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $fillable = [
        'user_id',
        'owner_id',
        'service_booking_id',
        'coupons_id',
        'transaction_id',
        'original_amount',
        'discount_amount',
        'final_amount',
        'method',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function serviceBooking()
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupons_id');
    }
    public function paymentSplit()
    {
        return $this->hasOne(PaymentSplit::class);
    }
    public function split()
    {
        return $this->hasOne(PaymentSplit::class);
    }

    public function ownerPayout()
    {
        return $this->hasOneThrough(
            OwnerPayout::class,
            PaymentSplit::class,
            'payment_id',
            'split_id',
            'id',
            'id'
        );
    }

     public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'payment_id');
    }
}