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
        'street_number',
        'house_number',
        'booking_date',
        'booking_hours',
        'address',
        'latitude',
        'longitude',
        'map_url',
        'quantity',
        'notes',
        'booking_status',
        'customer_status',
        'customer_completed_at',
        'auto_complete_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
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

    public function bookingProviders()
    {
        return $this->hasMany(ServiceBookingProvider::class, 'service_booking_id');
    }
    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
    
  

    // public function jobImages()
    // {
    //     return $this->hasMany(ServiceJobImage::class, 'service_booking_id');
    // }
}