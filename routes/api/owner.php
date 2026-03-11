<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TypeController;
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

     /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('categories')->group(function () {

        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/active', [CategoryController::class, 'activeCategories']);

    });


    Route::prefix('types')->group(function () {

        Route::get('/', [TypeController::class, 'index']);
        Route::get('/active', [TypeController::class, 'active']);
        Route::post('/', [TypeController::class, 'store']);
    
    });


         /*
    |--------------------------------------------------------------------------
    | Service  (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('services')->group(function () {

        Route::get('/', [ServiceController::class, 'index']);
    
        Route::post('/', [ServiceController::class, 'store']);
    
        Route::patch('/status/bulk', [ServiceController::class, 'updateManyStatus']);
    
        Route::get('/{service}', [ServiceController::class, 'myServices'])
            ->whereNumber('service');
    
        Route::put('/{service}', [ServiceController::class, 'update'])
            ->whereNumber('service');
    
        Route::delete('/{service}', [ServiceController::class, 'destroy'])
            ->whereNumber('service');
    });

});