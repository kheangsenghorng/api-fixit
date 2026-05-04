<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTaskGroup extends Model
{
    protected $fillable = [
        'package_id',
        'task_group_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function taskGroup()
    {
        return $this->belongsTo(TaskGroup::class);
    }
}