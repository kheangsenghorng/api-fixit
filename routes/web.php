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

use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {

    Mail::raw('This is a test email from Laravel Fixit API.', function ($message) {
        $message->to('senghorng0099p@gmail.com')
                ->subject('Laravel Test Email');
    });

    return "Email sent!";
});