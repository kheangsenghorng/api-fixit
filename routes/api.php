<?php

use App\Http\Controllers\Api\AdminOwnerDocumentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\GeocodeController;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// // OTP routes (rate limited)
// Route::post('/otp/send', [OtpController::class, 'send'])->middleware('throttle:3,1');
// Route::post('/otp/verify', [OtpController::class, 'verify']);
// Route::get('/geocode/reverse', [GeocodeController::class, 'reverse']);


/*
|--------------------------------------------------------------------------
| Protected Routes (JWT Required)
|--------------------------------------------------------------------------
*/

// Route::middleware(['auth:api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    // Route::get('/me', [AuthController::class, 'me']);
    // Route::post('/logout', [AuthController::class, 'logout']);
    // Route::post('/refresh', [AuthController::class, 'refresh']);


    /*
    |--------------------------------------------------------------------------
    | ADMIN Routes
    |--------------------------------------------------------------------------
    */

    // Route::middleware([IsActive::class, RoleMiddleware::class . ':admin'])
    //     ->group(function () {

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Users Management
    //     |--------------------------------------------------------------------------
    //     */

    //     Route::prefix('users')->group(function () {

    //         // Static routes
    //         Route::get('/', [UserController::class, 'index']);
    //         Route::post('/', [UserController::class, 'store']);
    //         Route::delete('/bulk', [UserController::class, 'destroyMany']);
    //         Route::patch('/status/bulk', [UserController::class, 'updateManyStatus']);

    //         // Parameter routes (restricted to numbers only)
    //         Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])
    //             ->whereNumber('user');

    //         Route::patch('/{user}/status', [UserController::class, 'updateStatus'])
    //             ->whereNumber('user');

    //         Route::patch('/{user}/toggle', [UserController::class, 'toggleStatus'])
    //             ->whereNumber('user');

    //         Route::get('/{user}', [UserController::class, 'show'])
    //             ->whereNumber('user');

    //         Route::put('/{user}', [UserController::class, 'update'])
    //             ->whereNumber('user');

    //         Route::delete('/{user}', [UserController::class, 'destroy'])
    //             ->whereNumber('user');
    //     });


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Owners Management (Admin Only)
    //     |--------------------------------------------------------------------------
    //     */

    //     Route::prefix('owners')->group(function () {

    //         Route::get('/', [OwnerController::class, 'index']);
    //         Route::post('/', [OwnerController::class, 'store']);
    //         Route::get('/{owner}', [OwnerController::class, 'show'])
    //             ->whereNumber('owner');
    //         Route::put('/{owner}', [OwnerController::class, 'update'])
    //             ->whereNumber('owner');
    //         Route::delete('/{owner}', [OwnerController::class, 'destroy'])
    //             ->whereNumber('owner');
    //     });

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Owner Documents (Admin Only)
    //     |--------------------------------------------------------------------------
    //     */
    //     Route::prefix('owner-documents')->group(function () {
    //         Route::get('/', [AdminOwnerDocumentController::class, 'index']);
    
    //         // ✅ approve/reject
    //         Route::patch('/{ownerDocument}/review', [AdminOwnerDocumentController::class, 'review'])
    //             ->whereNumber('ownerDocument');
    
    //         // ✅ OTP + download (if you use them)
    //         Route::post('/{ownerDocument}/otp', [AdminOwnerDocumentController::class, 'sendOtp'])
    //             ->whereNumber('ownerDocument');
    
    //         Route::post('/{ownerDocument}/verify-otp', [AdminOwnerDocumentController::class, 'verifyOtp'])
    //             ->whereNumber('ownerDocument');
    
    //         Route::get('/{ownerDocument}/download', [AdminOwnerDocumentController::class, 'download'])
    //             ->middleware('signed')
    //             ->whereNumber('ownerDocument')
    //             ->name('admin.owner-documents.download');      
                
                
    //     });
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Categories Management (Admin Only)
    //     |--------------------------------------------------------------------------
    //     */

    //     Route::prefix('categories')->group(function () {

    //         // Static routes
    //         Route::get('/', [CategoryController::class, 'index']);
    //         Route::post('/', [CategoryController::class, 'store']);

    //         Route::delete('/bulk', [CategoryController::class, 'destroyMany']);
    //         Route::patch('/status/bulk', [CategoryController::class, 'updateManyStatus']);

    //         // Soft delete extra routes
    //         Route::post('/{category}/restore', [CategoryController::class, 'restore'])
    //             ->whereNumber('category');

    //         Route::delete('/{category}/force', [CategoryController::class, 'forceDelete'])
    //             ->whereNumber('category');

    //         // Parameter routes
    //         Route::get('/{category}', [CategoryController::class, 'show'])
    //             ->whereNumber('category');

    //         Route::put('/{category}', [CategoryController::class, 'update'])
    //             ->whereNumber('category');

    //         Route::delete('/{category}', [CategoryController::class, 'destroy'])
    //             ->whereNumber('category');
    //     });

    // });

    
    // Route::middleware([IsActive::class, RoleMiddleware::class . ':owner'])
    //     ->prefix('owner')
    //     ->group(function () {

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Owner Documents (Owner Only)
    //     |--------------------------------------------------------------------------
    //     */
    //     Route::prefix('owner-documents')->group(function () {
    //         Route::get('/', [OwnerDocumentController::class, 'index']);
    //         Route::post('/', [OwnerDocumentController::class, 'store']);
    //         Route::get('/{ownerDocument}', [OwnerDocumentController::class, 'show'])->whereNumber('ownerDocument');
    //         Route::put('/{ownerDocument}', [OwnerDocumentController::class, 'update'])->whereNumber('ownerDocument');
    //         Route::delete('/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])->whereNumber('ownerDocument');
    //     });

    // });


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER Routes
    |--------------------------------------------------------------------------
    */

    // Route::middleware([IsActive::class, RoleMiddleware::class . ':customer'])
    //     ->prefix('customer')
    //     ->group(function () {

    //         Route::get('/profile/{user}', [UserController::class, 'show']);
    //         Route::put('/profile/{user}', [UserController::class, 'update']);
    //         Route::post('/avatar/{user}', [UserController::class, 'updateAvatar']);
    //     });

// });





Route::prefix('')->group(function () {
    require __DIR__.'/api/public.php';
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/admin.php';
    require __DIR__.'/api/owner.php';
    require __DIR__.'/api/customer.php';
});
