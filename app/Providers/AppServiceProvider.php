<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\Type;
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
        Service::observe(ServiceObserver::class);
        Type::observe(TypeObserver::class);
    }
}