<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Type extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'icon',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // 🔎 Search
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {

            $q->where('name', 'like', "%{$search}%")

            ->orWhereHas('category', function ($cat) use ($search) {
                $cat->where('name', 'like', "%{$search}%");
            });

        });
    }

    // 📂 Category filter
    public function scopeCategory($query, $categoryId)
    {
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
    }

    // ⚡ Status filter
    public function scopeStatus($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    // 🔽 Sorting
    public function scopeSort($query, $sortBy, $sortOrder)
    {
        $sortBy = $sortBy ?? 'id';
        $sortOrder = $sortOrder ?? 'desc';

        $query->orderBy($sortBy, $sortOrder);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Icon Manually
    |--------------------------------------------------------------------------
    */

    public function deleteIcon()
    {
        if ($this->icon && Storage::disk('public')->exists($this->icon)) {
            Storage::disk('public')->delete($this->icon);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::deleting(function ($type) {

            // Delete icon
            if ($type->icon && Storage::disk('public')->exists($type->icon)) {
                Storage::disk('public')->delete($type->icon);
            }

            // Delete services under this type
            foreach ($type->services as $service) {
                $service->delete(); // service model deletes images
            }

        });
    }
}