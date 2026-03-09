<?php

use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', IsActive::class, RoleMiddleware::class . ':owner'])
    ->prefix('owner')
    ->group(function () {

    Route::prefix('owner-documents')->group(function () {

        Route::get('/', [OwnerDocumentController::class, 'index']);
        Route::post('/', [OwnerDocumentController::class, 'store']);
        Route::get('/{ownerDocument}', [OwnerDocumentController::class, 'show'])->whereNumber('ownerDocument');
        Route::put('/{ownerDocument}', [OwnerDocumentController::class, 'update'])->whereNumber('ownerDocument');
        Route::delete('/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])->whereNumber('ownerDocument');

    });

});