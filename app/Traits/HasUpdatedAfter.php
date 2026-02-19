<?php


// app/Traits/HasUpdatedAfter.php
namespace App\Traits;

use Carbon\Carbon;

trait HasUpdatedAfter
{
    public function scopeUpdatedAfter($query, $date)
    {
        if (!$date) {
            return $query;
        }

        return $query->where('updated_at', '>', Carbon::parse($date));
    }
}
