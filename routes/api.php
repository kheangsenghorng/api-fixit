<?php


use Illuminate\Support\Facades\Route;



Route::prefix('')->group(function () {
    require __DIR__.'/api/public.php';
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/admin.php';
    require __DIR__.'/api/owner.php';
    require __DIR__.'/api/customer.php';
    require __DIR__.'/api/protected.php';
});
