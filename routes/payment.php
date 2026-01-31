<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\PayWayController;

Route::get('/payment-test', function () {
    return 'payment routes loaded';
});

Route::prefix('payment')->group(function () {

    Route::post('/payway/card', [PayWayController::class, 'card']);
    Route::post('/payway/qr', [PayWayController::class, 'qr']);

    // ABA pushback
    Route::post('/payway/callback', [PayWayController::class, 'callback']);

    // Verify
    Route::post('/payway/check', [PayWayController::class, 'checkTransaction']);

});
