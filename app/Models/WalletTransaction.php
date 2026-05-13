<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'wallet_transaction_id';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'payment_id',
        'service_booking_id',
        'type',
        'method',
        'transaction_ref',
        'external_transaction_id',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function serviceBooking()
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }
}