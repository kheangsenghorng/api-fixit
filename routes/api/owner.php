<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CouponUsageController;
use App\Http\Controllers\Api\Owner\OwnerUserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Api\PaymentAccountController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\ServiceBookingProviderController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckOwnerDocument;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api',
    IsActive::class,
    RoleMiddleware::class . ':owner',
    'throttle:60,1',
])
    ->prefix('owner')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')->group(function () {
            Route::get('/', [OwnerUserController::class, 'index']);

            Route::post('/', [OwnerUserController::class, 'store'])
                ->middleware('throttle:20,1');

            Route::get('/{user}', [UserController::class, 'show'])
                ->whereNumber('user');

            Route::put('/{user}', [UserController::class, 'update'])
                ->middleware('throttle:20,1')
                ->whereNumber('user');

            Route::delete('/{user}', [UserController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->whereNumber('user');

            Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])
                ->middleware('throttle:10,1')
                ->whereNumber('user');
        });

        /*
        |--------------------------------------------------------------------------
        | Owners
        |--------------------------------------------------------------------------
        */

        Route::prefix('owners')->group(function () {
            Route::post('/', [OwnerController::class, 'store'])
                ->middleware('throttle:10,1');

            Route::get('/{owner}', [OwnerController::class, 'show'])
                ->whereNumber('owner');

            Route::put('/{owner}', [OwnerController::class, 'update'])
                ->middleware('throttle:20,1')
                ->whereNumber('owner');

            Route::delete('/{owner}', [OwnerController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->whereNumber('owner');

            Route::delete('/{owner}/image', [OwnerController::class, 'deleteImage'])
                ->middleware('throttle:10,1')
                ->whereNumber('owner');
        });

        /*
        |--------------------------------------------------------------------------
        | Owner Documents
        |--------------------------------------------------------------------------
        */

        Route::prefix('owner-documents')->group(function () {
            Route::get('/', [OwnerDocumentController::class, 'index']);

            Route::post('/', [OwnerDocumentController::class, 'store'])
                ->middleware('throttle:10,1');

            Route::get('/{ownerDocument}', [OwnerDocumentController::class, 'show'])
                ->whereNumber('ownerDocument');

            Route::put('/{ownerDocument}', [OwnerDocumentController::class, 'update'])
                ->middleware('throttle:10,1')
                ->whereNumber('ownerDocument');

            Route::delete('/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->whereNumber('ownerDocument');
        });

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/active', [CategoryController::class, 'activeCategories']);

            Route::post('/', [CategoryController::class, 'store'])
                ->middleware('throttle:20,1');
        });

        /*
        |--------------------------------------------------------------------------
        | Types
        |--------------------------------------------------------------------------
        */

        Route::prefix('types')->group(function () {
            Route::get('/', [TypeController::class, 'index']);
            Route::get('/active', [TypeController::class, 'active']);

            Route::post('/', [TypeController::class, 'store'])
                ->middleware('throttle:20,1');
        });

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        Route::prefix('services')->group(function () {
            Route::get('/', [ServiceController::class, 'myServices']);
            Route::get('/stats', [ServiceController::class, 'serviceStats']);

            Route::post('/', [ServiceController::class, 'store'])
                ->middleware([
                    CheckOwnerDocument::class,
                    'throttle:10,1',
                ]);

            Route::patch('/status/bulk', [ServiceController::class, 'updateManyStatus'])
                ->middleware('throttle:10,1');

            Route::get('/{service}', [ServiceController::class, 'show'])
                ->whereNumber('service');

            Route::put('/{service}', [ServiceController::class, 'update'])
                ->middleware('throttle:20,1')
                ->whereNumber('service');

            Route::delete('/{service}', [ServiceController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->whereNumber('service');

            Route::delete('/{service}/image', [ServiceController::class, 'deleteImage'])
                ->middleware('throttle:10,1')
                ->whereNumber('service');
        });

        /*
        |--------------------------------------------------------------------------
        | Coupon Usages
        |--------------------------------------------------------------------------
        */

        Route::apiResource('coupon-usages', CouponUsageController::class)
            ->middleware('throttle:30,1');

        /*
        |--------------------------------------------------------------------------
        | Coupons
        |--------------------------------------------------------------------------
        */

        Route::prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);

            Route::get('/show-by-owner/{owner_id}', [CouponController::class, 'showByIdOwner'])
                ->whereNumber('owner_id');

            Route::get('/stats-by-owner', [CouponController::class, 'statsByIdOwner']);

            Route::get('/{coupon}', [CouponController::class, 'show'])
                ->whereNumber('coupon');

            Route::put('/{coupon}', [CouponController::class, 'update'])
                ->middleware('throttle:20,1')
                ->whereNumber('coupon');

            Route::delete('/{coupon}', [CouponController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->whereNumber('coupon');
        });

        /*
        |--------------------------------------------------------------------------
        | Service Bookings
        |--------------------------------------------------------------------------
        */

        Route::prefix('service-bookings')->group(function () {
            Route::get('/owner/{ownerId}', [ServiceBookingController::class, 'showByOwnerId'])
                ->whereNumber('ownerId');

            Route::get('/owner/{ownerId}/history', [ServiceBookingController::class, 'showHistoryByOwnerId'])
                ->whereNumber('ownerId');
            Route::get('/owner/{ownerId}/stats', [ServiceBookingController::class, 'bookingStatsByOwnerId'])
                ->whereNumber('ownerId');    
            Route::get('/{service_booking}', [ServiceBookingController::class, 'show'])
                ->whereNumber('service_booking');

            Route::match(['put', 'patch'], '/{service_booking}', [ServiceBookingController::class, 'update'])
                ->middleware('throttle:20,1')
                ->whereNumber('service_booking');
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Accounts
        |--------------------------------------------------------------------------
        */

        Route::prefix('payment-accounts')->group(function () {
            Route::get('/', [PaymentAccountController::class, 'index']);

            Route::post('/', [PaymentAccountController::class, 'store'])
                ->middleware('throttle:10,1');

            Route::get('/user/{userId}', [PaymentAccountController::class, 'showByUser'])
                ->whereNumber('userId');

            Route::get('/check-company/{userId}', [PaymentAccountController::class, 'checkCompanyBankAccount'])
                ->middleware('throttle:190,1')
                ->whereNumber('userId');

            Route::get('/{id}', [PaymentAccountController::class, 'show'])
                ->whereNumber('id');

            Route::match(['put', 'patch'], '/{id}', [PaymentAccountController::class, 'update'])
                ->middleware('throttle:10,1')
                ->whereNumber('id');

            Route::delete('/{id}', [PaymentAccountController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->whereNumber('id');
        });


        /*
        |--------------------------------------------------------------------------
        | Payment Accounts
        |--------------------------------------------------------------------------
        */

     Route::prefix('service-booking-providers')
        ->name('owner.service-booking-providers.')
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
 });