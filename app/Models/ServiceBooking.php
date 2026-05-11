<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'package_id',
        'address_id',
        'booking_date',
        'booking_hours',
        'quantity',
        'notes',
        'booking_status',
        'customer_status',
        'provider_completed_at',
        'customer_completed_at',
        'auto_complete_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'provider_completed_at' => 'datetime',
        'customer_completed_at' => 'datetime',
        'auto_complete_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function bookingProviders()
    {
        return $this->hasMany(ServiceBookingProvider::class, 'service_booking_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'service_booking_id');
    }
    public function payments()
{
    return $this->hasMany(Payment::class, 'service_booking_id');
}

    // public function jobImages()
    // {
    //     return $this->hasMany(ServiceJobImage::class, 'service_booking_id');
    // }
}