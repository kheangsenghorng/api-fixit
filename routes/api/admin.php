<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\AdminOwnerDocumentController;
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
    'throttle:60,1',
])->group(function () {

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('throttle:60,1');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('throttle:20,1');

        Route::delete('/bulk', [UserController::class, 'destroyMany'])
            ->middleware('throttle:5,1');

        Route::patch('/status/bulk', [UserController::class, 'updateManyStatus'])
            ->middleware('throttle:10,1');

        Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])
            ->middleware('throttle:10,1')
            ->whereNumber('user');

        Route::patch('/{user}/status', [UserController::class, 'updateStatus'])
            ->middleware('throttle:20,1')
            ->whereNumber('user');

        Route::patch('/{user}/toggle', [UserController::class, 'toggleStatus'])
            ->middleware('throttle:20,1')
            ->whereNumber('user');

        Route::get('/{user}', [UserController::class, 'show'])
            ->whereNumber('user');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('throttle:20,1')
            ->whereNumber('user');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('throttle:5,1')
            ->whereNumber('user');
    });

    Route::prefix('owners')->group(function () {
        Route::get('/', [OwnerController::class, 'index']);

        Route::post('/', [OwnerController::class, 'store'])
            ->middleware('throttle:20,1');

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
    | Owner Documents (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('owner-documents')->group(function () {
        Route::get('/', [AdminOwnerDocumentController::class, 'index']);

        Route::patch('/{ownerDocument}/review', [AdminOwnerDocumentController::class, 'review'])
            ->middleware('throttle:20,1')
            ->whereNumber('ownerDocument');

        Route::post('/{ownerDocument}/otp', [AdminOwnerDocumentController::class, 'sendOtp'])
            ->middleware('throttle:3,1')
            ->whereNumber('ownerDocument');

        Route::post('/notify-missing', [AdminOwnerDocumentController::class, 'notifyOwnerMissingDocuments'])
            ->middleware('throttle:5,1');

        Route::post('/{ownerDocument}/verify-otp', [AdminOwnerDocumentController::class, 'verifyOtp'])
            ->middleware('throttle:5,1')
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

        Route::post('/', [CategoryController::class, 'store'])
            ->middleware('throttle:20,1');

        Route::delete('/bulk', [CategoryController::class, 'destroyMany'])
            ->middleware('throttle:5,1');

        Route::patch('/status/bulk', [CategoryController::class, 'updateManyStatus'])
            ->middleware('throttle:10,1');

        Route::post('/{category}/restore', [CategoryController::class, 'restore'])
            ->middleware('throttle:10,1')
            ->whereNumber('category');

        Route::delete('/{category}/force', [CategoryController::class, 'forceDelete'])
            ->middleware('throttle:5,1')
            ->whereNumber('category');

        Route::get('/{category}', [CategoryController::class, 'show'])
            ->whereNumber('category');

        Route::put('/{category}', [CategoryController::class, 'update'])
            ->middleware('throttle:20,1')
            ->whereNumber('category');

        Route::delete('/{category}', [CategoryController::class, 'destroy'])
            ->middleware('throttle:5,1')
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

        Route::post('/', [TypeController::class, 'store'])
            ->middleware('throttle:20,1');

        Route::delete('/bulk', [TypeController::class, 'destroyMany'])
            ->middleware('throttle:5,1');

        Route::patch('/status/bulk', [TypeController::class, 'updateManyStatus'])
            ->middleware('throttle:10,1');

        Route::get('/{type}', [TypeController::class, 'show'])
            ->whereNumber('type');

        Route::put('/{type}', [TypeController::class, 'update'])
            ->middleware('throttle:20,1')
            ->whereNumber('type');

        Route::delete('/{type}', [TypeController::class, 'destroy'])
            ->middleware('throttle:5,1')
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
                'throttle:10,1',
            ]);

        Route::delete('/bulk', [ServiceController::class, 'destroyMany'])
            ->middleware('throttle:5,1');

        Route::delete('/{service}/image', [ServiceController::class, 'deleteImage'])
            ->middleware('throttle:10,1')
            ->whereNumber('service');

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
    });

    Route::apiResource('service-booking-providers', ServiceBookingProviderController::class)
        ->middleware('throttle:30,1');

    Route::get('service-booking-providers/booking/{bookingId}', [ServiceBookingProviderController::class, 'showByBookingId'])
        ->middleware('throttle:60,1')
        ->whereNumber('bookingId');

    Route::get('service-booking-providers/provider/{providerId}', [ServiceBookingProviderController::class, 'showByProviderId'])
        ->middleware('throttle:60,1')
        ->whereNumber('providerId');
});