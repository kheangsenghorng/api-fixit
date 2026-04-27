<?php

use App\Events\OwnerNotificationEvent;
use App\Http\Controllers\Api\AdminOwnerDocumentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Api\GeocodeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Auth\OtpEmailController;
use Illuminate\Support\Facades\Route;

// 🔐 Auth
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// 📲 OTP (phone)
Route::post('/otp/send', [OtpController::class, 'send'])
    ->middleware('throttle:3,1');

Route::post('/otp/verify', [OtpController::class, 'verify'])
    ->middleware('throttle:5,1');

// 📧 OTP (email)
Route::prefix('otp-email')->group(function () {
    Route::post('/send', [OtpEmailController::class, 'sendOtp'])
        ->middleware('throttle:3,1');

    Route::post('/verify', [OtpEmailController::class, 'verifyOtp'])
        ->middleware('throttle:5,1');

    Route::post('/resend', [OtpEmailController::class, 'resendOtp'])
        ->middleware('throttle:3,1');
});

// 🌍 Geocode
Route::get('/geocode/reverse', [GeocodeController::class, 'reverse'])
    ->middleware('throttle:30,1');

// 🧩 Category
Route::prefix('category')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/active', [CategoryController::class, 'activeCategories']);
    });

// 🧩 Type
Route::prefix('type')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/', [TypeController::class, 'index']);
        Route::get('/active', [TypeController::class, 'active']);
    });

// 🛠 Service
Route::prefix('service')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/active', [ServiceController::class, 'activeServices']);
        Route::get('/search-active-services', [ServiceController::class, 'searchActiveServices']);
        Route::get('/{service}/serviceId', [ServiceController::class, 'show']);
    });

// 📄 Signed download
Route::get('/owner-documents/{ownerDocument}/download', [AdminOwnerDocumentController::class, 'download'])
    ->middleware(['signed', 'throttle:20,1'])
    ->whereNumber('ownerDocument')
    ->name('admin.owner-documents.download');