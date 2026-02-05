<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\PayWayController;

Route::get('/payment-test', function () {
    return 'payment routes loaded';
});



Route::post('/payment/aba/getPaymentData', [PayWayController::class,'getPaymentData']);
Route::post('/payment/aba/check', [PayWayController::class,'checkTransactionV2']);
Route::post('/payment/aba/callback', [PayWayController::class,'callback']);
