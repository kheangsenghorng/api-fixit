<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerPayout extends Model
{
    protected $fillable = [
        'owner_id',
        'split_id',
        'amount',
        'method',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function split()
    {
        return $this->belongsTo(PaymentSplit::class, 'split_id');
    }
}