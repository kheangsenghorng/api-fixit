<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSplit extends Model
{
    protected $fillable = [
        'payment_id',
        'owner_id',
        'service_amount',
        'admin_commission',
        'owner_payout',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function payout()
    {
        return $this->hasOne(OwnerPayout::class, 'split_id');
    }
}