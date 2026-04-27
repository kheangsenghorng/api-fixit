<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api',
    'throttle:60,1',
])->group(function () {

    Route::get('/me', [AuthController::class, 'me'])
        ->middleware('throttle:60,1');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('throttle:20,1');

    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware('throttle:10,1');

});