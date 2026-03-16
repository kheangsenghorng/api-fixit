<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ImageUploadService;

class Service extends Model
{
    protected $fillable = [
        'owner_id',
        'category_id',
        'type_id',
        'title',
        'description',
        'status',
        'base_price',
        'duration',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        // Global scope: only services with active category + type
        static::addGlobalScope('activeCategoryType', function ($query) {

            $query->whereHas('category', function ($q) {
                $q->where('status', 'active');
            });

            $query->whereHas('type', function ($q) {
                $q->where('status', 'active');
            });

        });

        // Delete images when service deleted
        static::deleting(function ($service) {

            if (!empty($service->images)) {
                ImageUploadService::delete($service->images);
            }

        });
    }
}