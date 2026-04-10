<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CouponUsageController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])
    ->prefix('owner')
    ->group(function () {
        Route::post('/', [OwnerController::class, 'store']);
        Route::post('/document', [OwnerDocumentController::class, 'store']);
        Route::put('/document/{ownerDocument}', [OwnerDocumentController::class, 'update'])->whereNumber('ownerDocument');
        Route::delete('/document/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])->whereNumber('ownerDocument');
    });

    Route::prefix('v1/coupon-usages')->group(function () {

        Route::get('/', [CouponUsageController::class, 'index']);
    
        Route::get('/top-performing', [CouponUsageController::class, 'topPerformingCoupons']);
    
        Route::post('/', [CouponUsageController::class, 'store']);
    
        Route::get('/{couponUsage}', [CouponUsageController::class, 'show'])
            ->whereNumber('couponUsage');
    
        Route::put('/{couponUsage}', [CouponUsageController::class, 'update'])
            ->whereNumber('couponUsage');
    
        Route::delete('/{couponUsage}', [CouponUsageController::class, 'destroy'])
            ->whereNumber('couponUsage');
    });
    
    Route::prefix('v1/coupons')->group(function () {
    
        Route::get('/', [CouponController::class, 'index']);

        Route::get('/stats',[CouponController::class,'stats']);
    
        Route::post('/', [CouponController::class, 'store']);
    
        Route::get('/{coupon}', [CouponController::class, 'show'])
            ->whereNumber('coupon');
    
        Route::put('/{coupon}', [CouponController::class, 'update'])
            ->whereNumber('coupon');
    
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])
            ->whereNumber('coupon');
    });