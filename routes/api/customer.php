<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', IsActive::class, RoleMiddleware::class . ':customer'])
    ->prefix('customer')
    ->group(function () {

    Route::get('/profile/{user}', [UserController::class, 'show']);
    Route::put('/profile/{user}', [UserController::class, 'update']);
    Route::post('/avatar/{user}', [UserController::class, 'updateAvatar']);

});