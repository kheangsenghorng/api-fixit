<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OTP routes (rate limited)
Route::post('/otp/send', [OtpController::class, 'send'])->middleware('throttle:3,1');
Route::post('/otp/verify', [OtpController::class, 'verify']);


/*
|--------------------------------------------------------------------------
| Protected Routes (JWT Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);


    /*
    |--------------------------------------------------------------------------
    | ADMIN Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware([IsActive::class, RoleMiddleware::class . ':admin'])
        ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Users Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')->group(function () {

            // Static routes
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::delete('/bulk', [UserController::class, 'destroyMany']);
            Route::patch('/status/bulk', [UserController::class, 'updateManyStatus']);

            // Parameter routes (restricted to numbers only)
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


        /*
        |--------------------------------------------------------------------------
        | Owners Management (Admin Only)
        |--------------------------------------------------------------------------
        */

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

    });


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware([IsActive::class, RoleMiddleware::class . ':customer'])
        ->prefix('customer')
        ->group(function () {

            Route::get('/profile/{user}', [UserController::class, 'show']);
            Route::put('/profile/{user}', [UserController::class, 'update']);
            Route::post('/avatar/{user}', [UserController::class, 'updateAvatar']);
        });

});
