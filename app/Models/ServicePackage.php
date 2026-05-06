<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = [
        'service_id',
        'title',
        'description',
        'min_area_m2',
        'max_area_m2',
        'floor_number',
        'bedrooms',
        'duration_hours',
        'workers_count',
        'price',
        'billing_type',
        'status',
    ];

    protected $casts = [
        'min_area_m2' => 'decimal:2',
        'max_area_m2' => 'decimal:2',
        'duration_hours' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function taskGroups()
    {
        return $this->belongsToMany(
            TaskGroup::class,
            'package_task_groups',
            'package_id',
            'task_group_id'
        )->withPivot('sort_order')->withTimestamps();
    }

    public function includedItems()
    {
        return $this->belongsToMany(
            IncludedItem::class,
            'package_included_items',
            'package_id',
            'included_item_id'
        )
        ->withPivot('sort_order')
        ->withTimestamps()
        ->orderByPivot('sort_order');
    }
}