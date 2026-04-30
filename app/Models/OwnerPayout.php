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
        'transaction_reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function split()
    {
        return $this->belongsTo(PaymentSplit::class, 'split_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}