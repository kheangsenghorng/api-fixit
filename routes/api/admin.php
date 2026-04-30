<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\AdminOwnerDocumentController;
use App\Http\Controllers\Api\Admin\OwnerPayoutController as AdminOwnerPayoutController;
use App\Http\Controllers\Api\Admin\PaymentSplitController as AdminPaymentSplitController;
use App\Http\Controllers\Api\ServiceBookingProviderController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Middleware\CheckOwnerDocument;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api',
    IsActive::class,
    RoleMiddleware::class . ':admin',
])->group(function () {

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);

        Route::post('/', [UserController::class, 'store']);

        Route::delete('/bulk', [UserController::class, 'destroyMany']);

        Route::patch('/status/bulk', [UserController::class, 'updateManyStatus']);

        Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])
            ->whereNumber('user');

        Route::patch('/{user}/status', [UserController::class, 'updateStatus'])
            ->whereNumber('user');

        Route::patch('/{user}/toggle', [UserController::class, 'toggleStatus'])
            ->whereNumber('user');

        Route::get('/{user}', [UserController::class, 'show'])
            ->whereNumber('user');

        Route::put('/{user}', [UserController::class, 'update'])
            ->whereNumber('user');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->whereNumber('user');
    });

    Route::prefix('owners')->group(function () {
        Route::get('/', [OwnerController::class, 'index']);

        Route::post('/', [OwnerController::class, 'store']);

        Route::get('/{owner}', [OwnerController::class, 'show'])
            ->whereNumber('owner');

        Route::put('/{owner}', [OwnerController::class, 'update'])
            ->whereNumber('owner');

        Route::delete('/{owner}', [OwnerController::class, 'destroy'])
            ->whereNumber('owner');

        Route::delete('/{owner}/image', [OwnerController::class, 'deleteImage'])
            ->whereNumber('owner');
    });

    /*
    |--------------------------------------------------------------------------
    | Owner Documents (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('owner-documents')->group(function () {
        Route::get('/', [AdminOwnerDocumentController::class, 'index']);

        Route::patch('/{ownerDocument}/review', [AdminOwnerDocumentController::class, 'review'])
            ->whereNumber('ownerDocument');

        Route::post('/{ownerDocument}/otp', [AdminOwnerDocumentController::class, 'sendOtp'])
            ->whereNumber('ownerDocument');

        Route::post('/notify-missing', [AdminOwnerDocumentController::class, 'notifyOwnerMissingDocuments']);

        Route::post('/{ownerDocument}/verify-otp', [AdminOwnerDocumentController::class, 'verifyOtp'])
            ->whereNumber('ownerDocument');
    });

    /*
    |--------------------------------------------------------------------------
    | Categories (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/stats', [CategoryController::class, 'stats']);
        Route::get('/active', [CategoryController::class, 'activeCategories']);

        Route::post('/', [CategoryController::class, 'store']);

        Route::delete('/bulk', [CategoryController::class, 'destroyMany']);

        Route::patch('/status/bulk', [CategoryController::class, 'updateManyStatus']);

        Route::post('/{category}/restore', [CategoryController::class, 'restore'])
            ->whereNumber('category');

        Route::delete('/{category}/force', [CategoryController::class, 'forceDelete'])
            ->whereNumber('category');

        Route::get('/{category}', [CategoryController::class, 'show'])
            ->whereNumber('category');

        Route::put('/{category}', [CategoryController::class, 'update'])
            ->whereNumber('category');

        Route::delete('/{category}', [CategoryController::class, 'destroy'])
            ->whereNumber('category');
    });

    /*
    |--------------------------------------------------------------------------
    | Type (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('types')->group(function () {
        Route::get('/', [TypeController::class, 'index']);
        Route::get('/stats', [TypeController::class, 'stats']);
        Route::get('/active', [TypeController::class, 'active']);

        Route::post('/', [TypeController::class, 'store']);

        Route::delete('/bulk', [TypeController::class, 'destroyMany']);

        Route::patch('/status/bulk', [TypeController::class, 'updateManyStatus']);

        Route::get('/{type}', [TypeController::class, 'show'])
            ->whereNumber('type');

        Route::put('/{type}', [TypeController::class, 'update'])
            ->whereNumber('type');

        Route::delete('/{type}', [TypeController::class, 'destroy'])
            ->whereNumber('type');
    });

    /*
    |--------------------------------------------------------------------------
    | Service (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/active', [ServiceController::class, 'activeServices']);
        Route::get('/stats', [ServiceController::class, 'stats']);

        Route::post('/', [ServiceController::class, 'store'])
            ->middleware([
                CheckOwnerDocument::class,
            ]);

        Route::delete('/bulk', [ServiceController::class, 'destroyMany']);

        Route::delete('/{service}/image', [ServiceController::class, 'deleteImage'])
            ->whereNumber('service');

        Route::patch('/status/bulk', [ServiceController::class, 'updateManyStatus']);

        Route::get('/{service}', [ServiceController::class, 'show'])
            ->whereNumber('service');

        Route::put('/{service}', [ServiceController::class, 'update'])
            ->whereNumber('service');

        Route::delete('/{service}', [ServiceController::class, 'destroy'])
            ->whereNumber('service');
    });

    Route::apiResource('service-booking-providers', ServiceBookingProviderController::class);

    Route::get('service-booking-providers/booking/{bookingId}', [ServiceBookingProviderController::class, 'showByBookingId'])
        ->whereNumber('bookingId');

    Route::get('service-booking-providers/provider/{providerId}', [ServiceBookingProviderController::class, 'showByProviderId'])
        ->whereNumber('providerId');

    /*
    |--------------------------------------------------------------------------
    | Payment Splits & Owner Payouts (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('payment-splits')->group(function () {
        Route::get('/', [AdminPaymentSplitController::class, 'index']);
        Route::get('/stats', [AdminPaymentSplitController::class, 'stats']);
        Route::get('/{id}', [AdminPaymentSplitController::class, 'show'])
            ->whereNumber('id');
    });

    Route::prefix('owner-payouts')->group(function () {
        Route::get('/', [AdminOwnerPayoutController::class, 'index']);
        Route::get('/stats', [AdminOwnerPayoutController::class, 'stats']);
        Route::post('/pay-multiple-send-email', [AdminOwnerPayoutController::class, 'payMultipleAndSendEmail']);

        Route::patch('/{id}/status', [AdminOwnerPayoutController::class, 'updateStatus'])
            ->whereNumber('id');

        Route::get('/{id}', [AdminOwnerPayoutController::class, 'show'])
            ->whereNumber('id');
    });

});
