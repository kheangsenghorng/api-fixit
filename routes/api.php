<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
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
| Protected Routes (JWT + Active Account)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    /*
    |--------------------------------------------------------------------------
    | Users (ADMIN ONLY)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:api', IsActive::class, RoleMiddleware::class . ':admin'])
        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        ->prefix('users')
        ->group(function () {

        Route::get('/', [UserController::class, 'index']); // List users with pagination
        Route::post('/', [UserController::class, 'store']);// Create new user
        Route::delete('/bulk', [UserController::class, 'destroyMany']);// Bulk delete users
        Route::patch('/status/bulk', [UserController::class, 'updateManyStatus']);// Bulk update user statuses

        Route::post('/{user}/avatar', [UserController::class, 'updateAvatar']);// Update user avatar
        Route::patch('/{user}/status', [UserController::class, 'updateStatus']);// Update user active status
        Route::patch('/{user}/toggle', [UserController::class, 'toggleStatus']);// Toggle user active status

        Route::get('/{user}', [UserController::class, 'show']);// Get user details
        Route::put('/{user}', [UserController::class, 'update']);// Update user details
        Route::delete('/{user}', [UserController::class, 'destroy']);// Delete user


        /*
        |--------------------------------------------------------------------------
        | Owner (Create Only)
        |--------------------------------------------------------------------------
        */
        Route::prefix('owner')->group(function () {
            Route::post('/', [UserController::class, 'storeOwner']); // POST /api/owner
        });
    });
});
