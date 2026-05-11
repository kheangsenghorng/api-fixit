<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'street_number',
        'house_number',
        'city',
        'address',
        'latitude',
        'longitude',
        'map_url',
        'is_default',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceBookings()
    {
        return $this->hasMany(ServiceBooking::class, 'address_id');
    }
}