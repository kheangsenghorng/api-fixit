<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CouponUsageController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceBookingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('owner')->group(function () {
        Route::post('/', [OwnerController::class, 'store']);

        Route::post('/document', [OwnerDocumentController::class, 'store']);
        Route::put('/document/{ownerDocument}', [OwnerDocumentController::class, 'update'])
            ->whereNumber('ownerDocument');
        Route::delete('/document/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])
            ->whereNumber('ownerDocument');
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
        Route::get('/stats', [CouponController::class, 'stats']);
        Route::get('/{coupon}', [CouponController::class, 'show']);
        Route::get('/show-apply/{coupon}',[CouponController::class,'showApply']);
        Route::post('/', [CouponController::class, 'store']);
        

        Route::put('/{coupon}', [CouponController::class, 'update'])
            ->whereNumber('coupon');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])
            ->whereNumber('coupon');
    });

    Route::prefix('service-bookings')->group(function () {
        Route::get('/', [ServiceBookingController::class, 'index']);
        Route::post('/', [ServiceBookingController::class, 'store']);
    
        Route::get('/{service_booking}', [ServiceBookingController::class, 'show'])
            ->whereNumber('service_booking');
    
        Route::put('/{service_booking}', [ServiceBookingController::class, 'update'])
            ->whereNumber('service_booking');
    
        Route::patch('/{service_booking}', [ServiceBookingController::class, 'update'])
            ->whereNumber('service_booking');
    
        Route::delete('/{service_booking}', [ServiceBookingController::class, 'destroy'])
            ->whereNumber('service_booking');
    });


    Route::prefix('payments')->group(function () {
        Route::post('/khqr/individual', [PaymentController::class, 'generateIndividualKhqr']);
        Route::post('/khqr/merchant', [PaymentController::class, 'generateMerchantKhqr']);
        Route::post('/khqr/image', [PaymentController::class, 'generateKhqrImage']);
        Route::post('/khqr/deeplink', [PaymentController::class, 'generateDeeplink']);
        Route::post('/khqr/check-md5', [PaymentController::class, 'checkTransactionByMd5']);
        Route::post('/khqr/check-hash', [PaymentController::class, 'checkTransactionByHash']);
        Route::post('/khqr/check-account', [PaymentController::class, 'checkBakongAccount']);
        Route::post('/khqr/check-external-ref', [PaymentController::class, 'checkTransactionByExternalRef']);
        Route::post('/download-qr', [PaymentController::class, 'downloadPaymentQr']);
    });  
    Route::post('/generate-payment', [PaymentController::class, 'generatePayment']);
 });