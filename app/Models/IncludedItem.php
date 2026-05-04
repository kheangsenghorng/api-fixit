<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncludedItem extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'image_url',
        'status',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function packages()
    {
        return $this->belongsToMany(
            ServicePackage::class,
            'package_included_items',
            'included_item_id',
            'package_id'
        )->withPivot('sort_order')->withTimestamps();
    }
}