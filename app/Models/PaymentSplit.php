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
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function ownerPayout()
    {
        return $this->hasOne(OwnerPayout::class, 'split_id');
    }
}