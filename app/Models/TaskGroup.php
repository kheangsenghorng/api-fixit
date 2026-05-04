<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskGroup extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'status',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function taskItems()
    {
        return $this->hasMany(TaskItem::class);
    }

    public function packages()
    {
        return $this->belongsToMany(
            ServicePackage::class,
            'package_task_groups',
            'task_group_id',
            'package_id'
        )->withPivot('sort_order')->withTimestamps();
    }
}