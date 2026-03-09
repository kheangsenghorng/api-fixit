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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

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

    public function deleteIcon()
    {
        if ($this->icon && Storage::disk('public')->exists($this->icon)) {
            Storage::disk('public')->delete($this->icon);
        }
    }
}