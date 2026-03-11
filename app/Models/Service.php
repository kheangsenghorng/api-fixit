<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    // relationships
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
}