<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CouponUsageController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\ServiceBookingProviderController;
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
        Route::get('/show-apply/{coupon}', [CouponController::class, 'showApply']);
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

    Route::apiResource('payments', PaymentController::class);

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('/khqr/individual', [PaymentController::class, 'generateIndividualKhqr'])->name('khqr.individual');
        Route::post('/khqr/merchant', [PaymentController::class, 'generateMerchantKhqr'])->name('khqr.merchant');
        Route::post('/khqr/image', [PaymentController::class, 'generateKhqrImage'])->name('khqr.image');
        Route::post('/khqr/deeplink', [PaymentController::class, 'generateDeeplink'])->name('khqr.deeplink');
        Route::post('/khqr/check-md5', [PaymentController::class, 'checkTransactionByMd5'])->name('khqr.check-md5');
        Route::post('/khqr/check-hash', [PaymentController::class, 'checkTransactionByHash'])->name('khqr.check-hash');
        Route::post('/khqr/check-account', [PaymentController::class, 'checkBakongAccount'])->name('khqr.check-account');
        Route::post('/khqr/check-external-ref', [PaymentController::class, 'checkTransactionByExternalRef'])->name('khqr.check-external-ref');
        Route::post('/download-qr', [PaymentController::class, 'downloadPaymentQr'])->name('download-qr');
    });

    Route::post('/generate-payment', [PaymentController::class, 'generatePayment'])
        ->name('generate-payment');

    Route::prefix('providers')->group(function () {
        Route::get('/', [ProviderController::class, 'index']);
        Route::post('/', [ProviderController::class, 'store']);
        Route::get('/{id}', [ProviderController::class, 'show']);
          // Find providers by owner
        Route::get('/owner/{ownerId}', [ProviderController::class, 'findByOwner']);
         // Find providers by owner check provider
        Route::get('/check-provider/owner/{ownerId}/check', [ProviderController::class, 'findByOwnerCheckProvider']);

        Route::put('/{id}', [ProviderController::class, 'update']);
        Route::patch('/{id}', [ProviderController::class, 'update']);
        Route::delete('/{id}', [ProviderController::class, 'destroy']);
    });

    // Route::middleware(['auth:api', 'role:admin,owner,provider'])
    // ->prefix('service-booking-providers')
    // ->name('service-booking-providers.')
    // ->group(function () {
    //     Route::get('/', [ServiceBookingProviderController::class, 'index'])->name('index');
    //     Route::post('/', [ServiceBookingProviderController::class, 'store'])->name('store');

    //     Route::get('/booking/{bookingId}', [ServiceBookingProviderController::class, 'showByBookingId'])
    //         ->name('by-booking');

    //     Route::get('/provider/{providerId}', [ServiceBookingProviderController::class, 'showByProviderId'])
    //         ->name('by-provider');

    //     Route::get('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'show'])->name('show');
    //     Route::put('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'update'])->name('update');
    //     Route::patch('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'update'])->name('patch');
    //     Route::delete('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'destroy'])->name('destroy');
    // });
});
