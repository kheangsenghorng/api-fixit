<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageIncludedItem extends Model
{
    protected $fillable = [
        'package_id',
        'included_item_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function includedItem()
    {
        return $this->belongsTo(IncludedItem::class, 'included_item_id');
    }
}