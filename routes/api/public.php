<?php

use App\Http\Controllers\Api\AdminOwnerDocumentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Auth\OtpController;

use App\Http\Controllers\Api\GeocodeController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Auth\OtpEmailController;
use Illuminate\Support\Facades\Route;

// 🔐 Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
// Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
// 📲 OTP (phone)
Route::post('/otp/send', [OtpController::class, 'send'])->middleware('throttle:3,1');
Route::post('/otp/verify', [OtpController::class, 'verify']);

// Route::prefix('otp')->group(function () {
//     Route::post('/send', [OtpController::class, 'send'])->middleware('throttle:3,1');
//     Route::post('/verify', [OtpController::class, 'verify']);
//     Route::post('/resend', [OtpController::class, 'resend'])->middleware('throttle:3,1');
// });
// 📧 OTP (email)
Route::prefix('otp-email')->group(function () {
    Route::post('/send', [OtpEmailController::class, 'sendOtp'])->middleware('throttle:3,1');
    Route::post('/verify', [OtpEmailController::class, 'verifyOtp']);
    Route::post('/resend', [OtpEmailController::class, 'resendOtp'])->middleware('throttle:3,1');
});

// 🌍 Geocode
Route::get('/geocode/reverse', [GeocodeController::class, 'reverse']);


// 🧩 Category
Route::prefix('category')->group(function () {
    Route::get('/active', [CategoryController::class, 'activeCategories']);
});

// 🧩 Type
Route::prefix('type')->group(function () {
    Route::get('/active', [TypeController::class, 'active']);
});

// 🛠 Service
Route::prefix('service')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::get('/active', [ServiceController::class, 'activeServices']);
});

// 📄 Signed download
Route::get('/owner-documents/{ownerDocument}/download',
    [AdminOwnerDocumentController::class, 'download'])
    ->middleware('signed')
    ->whereNumber('ownerDocument')
    ->name('admin.owner-documents.download');