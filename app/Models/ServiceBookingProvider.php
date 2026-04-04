<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBookingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_booking_id',
        'provider_id',
        'assigned_by',
        'role',
        'status',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function serviceBooking()
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function assignedByOwner()
    {
        return $this->belongsTo(Owner::class, 'assigned_by');
    }
}