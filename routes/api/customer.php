<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', IsActive::class, RoleMiddleware::class . ':customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        Route::get('/profile/{user}', [UserController::class, 'show'])->name('profile.show');
        Route::put('/profile/{user}', [UserController::class, 'update'])->name('profile.update');
        Route::post('/avatar/{user}', [UserController::class, 'updateAvatar'])->name('avatar.update');

        Route::prefix('service-bookings')
            ->name('service-bookings.')
            ->group(function () {
                Route::get('/user/{userId}', [ServiceBookingController::class, 'showByUserId'])->name('showByUserId');
                Route::post('/', [ServiceBookingController::class, 'store'])->name('store');
            });

        Route::apiResource('payments', PaymentController::class);
    });