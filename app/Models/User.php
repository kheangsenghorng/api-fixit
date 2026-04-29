<?php

namespace App\Models;

use App\Traits\HasUpdatedAfter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUpdatedAfter;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'is_active',
        'owner_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | JWT REQUIRED METHODS
    |--------------------------------------------------------------------------
    */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function isProvider()
    {
        return $this->role === 'provider';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // For user with role owner:
    // users.id -> owners.user_id
    public function ownerProfile()
    {
        return $this->hasOne(Owner::class, 'user_id');
    }

    // For users created under an owner:
    // users.owner_id -> owners.id
    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

     public function serviceBookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }
}