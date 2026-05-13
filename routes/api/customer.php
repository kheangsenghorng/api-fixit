<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\ServiceBookingProviderController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WalletTransactionController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([
        'auth:api',
        IsActive::class,
        RoleMiddleware::class . ':customer',
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

        Route::prefix('service-bookings')->name('service-bookings.')->group(function () {
            Route::get('/user/{userId}', [ServiceBookingController::class, 'showByUserId'])
    
                ->whereNumber('userId')
                ->name('showByUserId');
        
            Route::get('/{serviceBooking}', [ServiceBookingController::class, 'show'])
             
                ->whereNumber('serviceBooking')
                ->name('show');
        
            Route::post('/', [ServiceBookingController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('store');
        });

        Route::apiResource('user-addresses', UserAddressController::class);

        Route::patch(
            'user-addresses/{id}/set-default',
            [UserAddressController::class, 'setDefault']
        );

        Route::apiResource('payments', PaymentController::class);

        Route::prefix('service-booking-providers')
        ->name('service-booking-providers.')
            ->group(function () {
            Route::get('/', [ServiceBookingProviderController::class, 'index'])->name('index');
            Route::post('/', [ServiceBookingProviderController::class, 'store'])->name('store');

            Route::get('/booking/{bookingId}', [ServiceBookingProviderController::class, 'showByBookingId'])
                ->name('by-booking');

            Route::get('/provider/{providerId}', [ServiceBookingProviderController::class, 'showByProviderId'])
                ->name('by-provider');

            Route::get('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'show'])->name('show');
            Route::put('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'update'])->name('update');
            Route::patch('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'update'])->name('patch');
            Route::delete('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'destroy'])->name('destroy');
      });  
      Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::post('/', [ReviewController::class, 'store'])->name('store');

            Route::get('/booking/{bookingId}', [ReviewController::class, 'showByBookingId'])
                ->name('by-booking');

            Route::get('/user/{userId}', [ReviewController::class, 'showByUserId'])
                ->name('by-user');

            Route::get('/{review}', [ReviewController::class, 'show'])->name('show');
            Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
            Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
        });  

        Route::prefix('wallets')->group(function () {
            Route::get('/', [WalletController::class, 'index']);
            Route::post('/', [WalletController::class, 'store']);
        
            Route::get('/user/{userId}', [WalletController::class, 'showByUserId']);
            Route::get('/{walletId}', [WalletController::class, 'show']);
        
            Route::put('/{walletId}', [WalletController::class, 'update']);
            Route::post('/{walletId}/top-up', [WalletController::class, 'topUp']);
        
            Route::patch('/{walletId}/freeze', [WalletController::class, 'freeze']);
            Route::patch('/{walletId}/activate', [WalletController::class, 'activate']);
        
            Route::delete('/{walletId}', [WalletController::class, 'destroy']);
        });
        Route::prefix('wallet-transactions')->group(function () {
            Route::get('/', [WalletTransactionController::class, 'index']);
            Route::post('/', [WalletTransactionController::class, 'store']);
        
            Route::get('/user/{userId}', [WalletTransactionController::class, 'showByUserId']);
            Route::get('/wallet/{walletId}', [WalletTransactionController::class, 'showByWalletId']);
        
            Route::get('/{id}', [WalletTransactionController::class, 'show']);
            Route::delete('/{id}', [WalletTransactionController::class, 'destroy']);
        });
    });