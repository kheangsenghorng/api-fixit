<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\ServiceBookingProvider;
use App\Models\Type;
use App\Observers\CategoryObserver;
use App\Observers\CouponUsageObserver;
use App\Observers\PaymentObserver;
use App\Observers\ServiceBookingObserver;
use App\Observers\ServiceBookingProviderObserver;
use App\Observers\ServiceObserver;
use App\Observers\TypeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        Service::observe(ServiceObserver::class);
        Type::observe(TypeObserver::class);

        ServiceBookingProvider::observe(ServiceBookingProviderObserver::class);
        CouponUsage::observe(CouponUsageObserver::class);
        ServiceBooking::observe(ServiceBookingObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}