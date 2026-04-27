<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $primaryKey = 'providerId';

    protected $fillable = [
        'user_id',
        'owner_id',
        'category_id',
        'provider_type',
        'status',
        'rating',
        'total_jobs',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function bookingProviders()
{
    return $this->hasMany(ServiceBookingProvider::class, 'provider_id');
}
}