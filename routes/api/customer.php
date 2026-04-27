<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([
        'auth:api',
        IsActive::class,
        RoleMiddleware::class . ':customer',
        'throttle:60,1',
    ])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        Route::get('/profile/{user}', [UserController::class, 'show'])
            ->middleware('throttle:60,1')
            ->whereNumber('user')
            ->name('profile.show');

        Route::put('/profile/{user}', [UserController::class, 'update'])
            ->middleware('throttle:20,1')
            ->whereNumber('user')
            ->name('profile.update');

        Route::post('/avatar/{user}', [UserController::class, 'updateAvatar'])
            ->middleware('throttle:10,1')
            ->whereNumber('user')
            ->name('avatar.update');

        Route::prefix('service-bookings')
            ->name('service-bookings.')
            ->group(function () {
                Route::get('/user/{userId}', [ServiceBookingController::class, 'showByUserId'])
                    ->middleware('throttle:60,1')
                    ->whereNumber('userId')
                    ->name('showByUserId');

                Route::post('/', [ServiceBookingController::class, 'store'])
                    ->middleware('throttle:10,1')
                    ->name('store');
            });

        Route::apiResource('payments', PaymentController::class)
            ->middleware('throttle:20,1');
    });