<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'service_booking_id',
        'user_id',
        'rating',
        'comment',
    ];
}