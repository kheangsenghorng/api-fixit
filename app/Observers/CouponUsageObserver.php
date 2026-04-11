<?php

namespace App\Observers;

use App\Events\CouponUsageUpdated;
use App\Models\CouponUsage;

class CouponUsageObserver
{
    public function created(CouponUsage $couponUsage): void
    {
        event(new CouponUsageUpdated($couponUsage, 'created'));
    }

    public function updated(CouponUsage $couponUsage): void
    {
        event(new CouponUsageUpdated($couponUsage, 'updated'));
    }

    public function deleted(CouponUsage $couponUsage): void
    {
        event(new CouponUsageUpdated($couponUsage, 'deleted'));
    }
}