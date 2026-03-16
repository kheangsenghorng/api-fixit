<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function types()
    {
        return $this->hasMany(Type::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::deleting(function ($category) {

            // delete types (types will delete services)
            foreach ($category->types as $type) {
                $type->delete();
            }

            // delete services directly under category
            foreach ($category->services as $service) {
                $service->delete();
            }

        });
    }
}