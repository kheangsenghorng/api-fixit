<?php


use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])
    ->prefix('owner')
    ->group(function () {
        Route::post('/', [OwnerController::class, 'store']);
        Route::post('/document', [OwnerDocumentController::class, 'store']);
        Route::put('/document/{ownerDocument}', [OwnerDocumentController::class, 'update'])->whereNumber('ownerDocument');
        Route::delete('/document/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])->whereNumber('ownerDocument');
    });