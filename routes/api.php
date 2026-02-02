<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OTP routes with rate limiting
Route::post('/otp/send', [OtpController::class, 'send'])
    ->middleware('throttle:3,1');
// Verify OTP
Route::post('/otp/verify', [OtpController::class, 'verify']);


// Protected routes
Route::middleware(['auth:api'])->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);


});
