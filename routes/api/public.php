<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\GeocodeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/otp/send', [OtpController::class, 'send'])->middleware('throttle:3,1');
Route::post('/otp/verify', [OtpController::class, 'verify']);

Route::get('/geocode/reverse', [GeocodeController::class, 'reverse']);

Route::get('/test-event', [TestController::class, 'send']);