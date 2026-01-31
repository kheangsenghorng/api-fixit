<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\PayWayController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/pay', [PayWayController::class,'index']);

Route::post('/pay/card', [PayWayController::class,'card']);

Route::get('/pay/success', [PayWayController::class,'success']);
Route::get('/pay/cancel', [PayWayController::class,'cancel']);

Route::post('/pay/callback', [PayWayController::class,'callback']);
