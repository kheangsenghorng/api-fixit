<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CouponUsageController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckOwnerDocument;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', IsActive::class, RoleMiddleware::class . ':owner'])
    ->prefix('owner')
    ->group(function () {

    Route::prefix('users')->group(function () {

        Route::get('/{user}', [UserController::class, 'show'])->whereNumber('user');
        Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])->whereNumber('user');
        Route::put('/{user}', [UserController::class, 'update'])->whereNumber('user');
        Route::delete('/{user}', [UserController::class, 'destroy'])->whereNumber('user');

    });


   /*
    |--------------------------------------------------------------------------
    | Owners
    |--------------------------------------------------------------------------
    */

    Route::prefix('owners')->group(function () {

        Route::post('/', [OwnerController::class, 'store']);
        Route::get('/{owner}', [OwnerController::class, 'show'])->whereNumber('owner');
        Route::put('/{owner}', [OwnerController::class, 'update'])->whereNumber('owner');
        Route::delete('/{owner}', [OwnerController::class, 'destroy'])->whereNumber('owner');
        Route::delete('/{owner}/image', [OwnerController::class, 'deleteImage']);
    });

    Route::prefix('owner-documents')->group(function () {

        Route::get('/', [OwnerDocumentController::class, 'index']);
        Route::post('/', [OwnerDocumentController::class, 'store']);
        Route::get('/{ownerDocument}', [OwnerDocumentController::class, 'show'])->whereNumber('ownerDocument');
        Route::put('/{ownerDocument}', [OwnerDocumentController::class, 'update'])->whereNumber('ownerDocument');
        Route::delete('/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])->whereNumber('ownerDocument');

    });

     /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('categories')->group(function () {

        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/active', [CategoryController::class, 'activeCategories']);

    });


    Route::prefix('types')->group(function () {
        Route::get('/', [TypeController::class, 'index']);
        Route::get('/active', [TypeController::class, 'active']);
        Route::post('/', [TypeController::class, 'store']);
    
    });


     /*
    |--------------------------------------------------------------------------
    | Service  owner
    |--------------------------------------------------------------------------
    */
    Route::prefix('services')->group(function () {

        Route::get('/', [ServiceController::class, 'myServices']);

        Route::get('/{service}', [ServiceController::class, 'show']);

        Route::get('/stats', [ServiceController::class, 'serviceStats']);

        // Require OwnerDocument before creating service
        Route::post('/', [ServiceController::class, 'store'])
         ->middleware(CheckOwnerDocument::class);


        Route::patch('/status/bulk', [ServiceController::class, 'updateManyStatus']);

        Route::get('/{service}', [ServiceController::class, 'show'])
            ->whereNumber('service');

        Route::put('/{service}', [ServiceController::class, 'update'])
            ->whereNumber('service');

        Route::delete('/{service}', [ServiceController::class, 'destroy'])
            ->whereNumber('service');

        Route::delete('/{service}/image  ', [ServiceController::class, 'deleteImage']);   
    });

    // service bookings
    Route::prefix('service-bookings')->group(function(){
        Route::get('/owner/{ownerId}',[ServiceBookingController::class, 'showByIdOwner']);
        Route::patch('/',[ServiceBookingController::class, 'showByIdOwner']);
     });



     ///coupons
    Route::apiResource('coupon-usages', CouponUsageController::class);

    Route::prefix('coupons')->group(function () {
    
        Route::get('/', [CouponController::class, 'index']);

        Route::get('/show-by-owner/{owner_id}',[CouponController::class, 'showByIdOwner']);

        Route::get('/stats-by-owner',[CouponController::class,'statsByIdOwner']);


        Route::get('/{coupon}', [CouponController::class, 'show'])
            ->whereNumber('coupon');
    
        Route::put('/{coupon}', [CouponController::class, 'update'])
            ->whereNumber('coupon');
    
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])
            ->whereNumber('coupon');
    });

    Route::prefix('service-bookings')->group(function () {
     
        Route::get('/{service_booking}', [ServiceBookingController::class, 'show'])
            ->whereNumber('service_booking');
        Route::get('/owner/{ownerId}', [ServiceBookingController::class, 'showByOwnerId']);    
        Route::get('/owner/{ownerId}/history', [ServiceBookingController::class, 'showHistoryByOwnerId']);
        Route::put('/{service_booking}', [ServiceBookingController::class, 'update'])
            ->whereNumber('service_booking');
    
        Route::patch('/{service_booking}', [ServiceBookingController::class, 'update'])
            ->whereNumber('service_booking');
    });


});