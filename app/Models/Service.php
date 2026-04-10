<?php

namespace App\Models;

use App\Services\ImageUploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    protected $fillable = [
        'owner_id',
        'category_id',
        'type_id',
        'title',
        'description',
        'base_price',
        'status',
        'images',
        'duration',
        'images',
        'lat',
        'lng',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    protected static function booted()
    {
        // Apply active category/type filter only for guest or customer
        static::addGlobalScope('activeCategoryType', function (Builder $query) {
            $user = Auth::user();

            if (!$user || $user->role === 'customer') {
                $query->whereHas('category', function ($q) {
                    $q->where('status', 'active');
                });

                $query->whereHas('type', function ($q) {
                    $q->where('status', 'active');
                });
            }
        });

        // Delete images when service deleted
        static::deleting(function ($service) {
            if (!empty($service->images)) {
                ImageUploadService::delete($service->images);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }
}