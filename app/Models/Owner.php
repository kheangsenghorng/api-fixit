<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'business_name',
        'address',
        'images',
        'logo',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * Owner belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('business_name', 'like', "%{$search}%");
        }

        return $query;
    }

    public function scopeFilterUser($query, $userId)
    {
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    public function scopeFilterTrashed($query, $trashed)
    {
        if ($trashed === 'only') {
            $query->onlyTrashed();
        } elseif ($trashed === 'with') {
            $query->withTrashed();
        }

        return $query;
    }
}
