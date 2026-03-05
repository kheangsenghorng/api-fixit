<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\AdminOwnerDocumentController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', IsActive::class, RoleMiddleware::class . ':admin'])
    ->group(function () {

    Route::prefix('users')->group(function () {

        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);

        Route::delete('/bulk', [UserController::class, 'destroyMany']);
        Route::patch('/status/bulk', [UserController::class, 'updateManyStatus']);

        Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])->whereNumber('user');
        Route::patch('/{user}/status', [UserController::class, 'updateStatus'])->whereNumber('user');
        Route::patch('/{user}/toggle', [UserController::class, 'toggleStatus'])->whereNumber('user');

        Route::get('/{user}', [UserController::class, 'show'])->whereNumber('user');
        Route::put('/{user}', [UserController::class, 'update'])->whereNumber('user');
        Route::delete('/{user}', [UserController::class, 'destroy'])->whereNumber('user');

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
    });


    /*
    |--------------------------------------------------------------------------
    | Owner Documents (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('owner-documents')->group(function () {
        Route::get('/', [AdminOwnerDocumentController::class, 'index']);

        // ✅ approve/reject
        Route::patch('/{ownerDocument}/review', [AdminOwnerDocumentController::class, 'review'])
            ->whereNumber('ownerDocument');

        // ✅ OTP + download (if you use them)
        Route::post('/{ownerDocument}/otp', [AdminOwnerDocumentController::class, 'sendOtp'])
            ->whereNumber('ownerDocument');

        Route::post('/{ownerDocument}/verify-otp', [AdminOwnerDocumentController::class, 'verifyOtp'])
            ->whereNumber('ownerDocument');

        Route::get('/{ownerDocument}/download', [AdminOwnerDocumentController::class, 'download'])
            ->middleware('signed')
            ->whereNumber('ownerDocument')
            ->name('admin.owner-documents.download');      
            
            
    });

    /*
    |--------------------------------------------------------------------------
    | Categories  (Admin Only)
    |--------------------------------------------------------------------------
    */

    Route::prefix('categories')->group(function () {

        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);

        Route::delete('/bulk', [CategoryController::class, 'destroyMany']);
        Route::patch('/status/bulk', [CategoryController::class, 'updateManyStatus']);

        Route::post('/{category}/restore', [CategoryController::class, 'restore'])->whereNumber('category');
        Route::delete('/{category}/force', [CategoryController::class, 'forceDelete'])->whereNumber('category');

        Route::get('/{category}', [CategoryController::class, 'show'])->whereNumber('category');
        Route::put('/{category}', [CategoryController::class, 'update'])->whereNumber('category');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->whereNumber('category');

    });


    /*
    |--------------------------------------------------------------------------
    | Type  (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('types')->group(function () {

        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);

        Route::delete('/bulk', [CategoryController::class, 'destroyMany']);
        Route::patch('/status/bulk', [CategoryController::class, 'updateManyStatus']);

        Route::post('/{types}/restore', [CategoryController::class, 'restore'])->whereNumber('category');
        Route::delete('/{types}/force', [CategoryController::class, 'forceDelete'])->whereNumber('category');

        Route::get('/{types}', [CategoryController::class, 'show'])->whereNumber('category');
        Route::put('/{types}', [CategoryController::class, 'update'])->whereNumber('category');
        Route::delete('/{types}', [CategoryController::class, 'destroy'])->whereNumber('category');

    });



});