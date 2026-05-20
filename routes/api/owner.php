<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CouponUsageController;
use App\Http\Controllers\Api\IncludedItemController;
use App\Http\Controllers\Api\Owner\OwnerPayoutController;
use App\Http\Controllers\Api\Owner\OwnerUserController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\OwnerDocumentController;
use App\Http\Controllers\Api\PackageIncludedItemController;
use App\Http\Controllers\Api\PackageTaskGroupController;
use App\Http\Controllers\Api\PaymentAccountController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\ServiceBookingProviderController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServicePackageController;
use App\Http\Controllers\Api\TaskGroupController;
use App\Http\Controllers\Api\TaskItemController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckOwnerDocument;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api',
    IsActive::class,
    RoleMiddleware::class . ':owner',
])
    ->prefix('owner')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')->group(function () {
            Route::get('/', [OwnerUserController::class, 'index']);

            Route::post('/', [OwnerUserController::class, 'store']);

            Route::get('/{user}', [UserController::class, 'show'])
                ->whereNumber('user');

            Route::put('/{user}', [UserController::class, 'update'])
                ->whereNumber('user');

            Route::delete('/{user}', [UserController::class, 'destroy'])
                ->whereNumber('user');

            Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])
                ->whereNumber('user');
        });

        /*
        |--------------------------------------------------------------------------
        | Owners
        |--------------------------------------------------------------------------
        */

        Route::prefix('owners')->group(function () {
            Route::post('/', [OwnerController::class, 'store']);

            Route::get('/{owner}', [OwnerController::class, 'show'])
                ->whereNumber('owner');

            Route::put('/{owner}', [OwnerController::class, 'update'])
                ->whereNumber('owner');

            Route::delete('/{owner}', [OwnerController::class, 'destroy'])
                ->whereNumber('owner');

            Route::delete('/{owner}/image', [OwnerController::class, 'deleteImage'])
                ->whereNumber('owner');
        });

        /*
        |--------------------------------------------------------------------------
        | Owner Documents
        |--------------------------------------------------------------------------
        */

        Route::prefix('owner-documents')->group(function () {
            Route::get('/', [OwnerDocumentController::class, 'index']);

            Route::post('/', [OwnerDocumentController::class, 'store']);

            Route::get('/{ownerDocument}', [OwnerDocumentController::class, 'show'])
                ->whereNumber('ownerDocument');

            Route::put('/{ownerDocument}', [OwnerDocumentController::class, 'update'])
                ->whereNumber('ownerDocument');

            Route::delete('/{ownerDocument}', [OwnerDocumentController::class, 'destroy'])
                ->whereNumber('ownerDocument');
        });

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/active', [CategoryController::class, 'activeCategories']);

            Route::post('/', [CategoryController::class, 'store']);
        });

        /*
        |--------------------------------------------------------------------------
        | Types
        |--------------------------------------------------------------------------
        */

        Route::prefix('types')->group(function () {
            Route::get('/', [TypeController::class, 'index']);
            Route::get('/active', [TypeController::class, 'active']);

            Route::post('/', [TypeController::class, 'store']);
        });

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        Route::prefix('services')->group(function () {
            Route::get('/', [ServiceController::class, 'myServices']);
            Route::get('/stats', [ServiceController::class, 'serviceStats']);

            Route::post('/', [ServiceController::class, 'store'])
                ->middleware([
                    CheckOwnerDocument::class,
                ]);

            Route::patch('/status/bulk', [ServiceController::class, 'updateManyStatus']);

            Route::get('/{service}', [ServiceController::class, 'show'])
                ->whereNumber('service');

            Route::put('/{service}', [ServiceController::class, 'update'])
                ->whereNumber('service');

            Route::delete('/{service}', [ServiceController::class, 'destroy'])
                ->whereNumber('service');

            Route::delete('/{service}/image', [ServiceController::class, 'deleteImage'])
                ->whereNumber('service');
        });
        /*
        |--------------------------------------------------------------------------
        | Services packages
        |--------------------------------------------------------------------------
        */
        Route::prefix('service-packages')->name('owner.service-packages.')->group(function () {
            Route::get('/', [ServicePackageController::class, 'index'])->name('index');
        
            Route::get('/service/{serviceId}', [ServicePackageController::class, 'showByServiceId'])
                ->whereNumber('serviceId')
                ->name('by-service');

            Route::get('/service/{serviceId}/included-items', [ServicePackageController::class, 'getByServiceId'])
                ->whereNumber('serviceId')
                ->name('included-items.by-service');

            Route::post('/', [ServicePackageController::class, 'store'])->name('store');
        
            Route::patch('/status/bulk', [ServicePackageController::class, 'updateManyStatus'])
                ->name('status.bulk');
        
            Route::delete('/bulk', [ServicePackageController::class, 'destroyMany'])
                ->name('bulk.destroy');
        
            Route::get('/{servicePackage}', [ServicePackageController::class, 'show'])
                ->whereNumber('servicePackage')
                ->name('show');
        
            Route::put('/{servicePackage}', [ServicePackageController::class, 'update'])
                ->whereNumber('servicePackage')
                ->name('update');
        
            Route::delete('/{servicePackage}', [ServicePackageController::class, 'destroy'])
                ->whereNumber('servicePackage')
                ->name('destroy');
        });
        /*
        |--------------------------------------------------------------------------
        | task-groups
        |--------------------------------------------------------------------------
        */

        Route::prefix('task-groups')->name('owner.task-groups.')->group(function () {
            Route::get('/', [TaskGroupController::class, 'index'])
                ->name('index');
        
            Route::get('/service/{serviceId}', [TaskGroupController::class, 'showByServiceId'])
                ->whereNumber('serviceId')
                ->name('by-service');
        
            Route::post('/', [TaskGroupController::class, 'store'])
                ->name('store');
        
            Route::get('/{taskGroup}', [TaskGroupController::class, 'show'])
                ->whereNumber('taskGroup')
                ->name('show');
        
            Route::put('/{taskGroup}', [TaskGroupController::class, 'update'])
                ->whereNumber('taskGroup')
                ->name('update');
        
            Route::delete('/{taskGroup}', [TaskGroupController::class, 'destroy'])
                ->whereNumber('taskGroup')
                ->name('destroy');
        });
      

        Route::apiResource('task-items', TaskItemController::class);
         /*
        |--------------------------------------------------------------------------
        | included-items
        |--------------------------------------------------------------------------
        */

        Route::prefix('included-items')->name('owner.included-items.')->group(function () {
            Route::get('/', [IncludedItemController::class, 'index'])
                ->name('index');
        
            Route::get('/service/{serviceId}', [IncludedItemController::class, 'showByServiceId'])
                ->whereNumber('serviceId')
                ->name('by-service');
        
            Route::post('/', [IncludedItemController::class, 'store'])
                ->name('store');
        
            Route::get('/{includedItem}', [IncludedItemController::class, 'show'])
                ->whereNumber('includedItem')
                ->name('show');
        
            Route::put('/{includedItem}', [IncludedItemController::class, 'update'])
                ->whereNumber('includedItem')
                ->name('update');
        
            Route::delete('/{includedItem}', [IncludedItemController::class, 'destroy'])
                ->whereNumber('includedItem')
                ->name('destroy');
        });

        Route::apiResource('package-included-items', PackageIncludedItemController::class);
        Route::apiResource('package-task-groups', PackageTaskGroupController::class);
        /*
        |--------------------------------------------------------------------------
        | Coupon Usages
        |--------------------------------------------------------------------------
        */

        Route::apiResource('coupon-usages', CouponUsageController::class);

        /*
        |--------------------------------------------------------------------------
        | Coupons
        |--------------------------------------------------------------------------
        */

        Route::prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);

            Route::get('/show-by-owner/{owner_id}', [CouponController::class, 'showByIdOwner'])
                ->whereNumber('owner_id');

            Route::get('/stats-by-owner', [CouponController::class, 'statsByIdOwner']);

            Route::get('/{coupon}', [CouponController::class, 'show'])
                ->whereNumber('coupon');

            Route::put('/{coupon}', [CouponController::class, 'update'])
                ->whereNumber('coupon');

            Route::delete('/{coupon}', [CouponController::class, 'destroy'])
                ->whereNumber('coupon');
        });

        /*
        |--------------------------------------------------------------------------
        | Service Bookings
        |--------------------------------------------------------------------------
        */

        Route::prefix('service-bookings')->group(function () {
            Route::get('/owner/{ownerId}', [ServiceBookingController::class, 'showByOwnerId'])
                ->whereNumber('ownerId');

            Route::get('/owner/{ownerId}/history', [ServiceBookingController::class, 'showHistoryByOwnerId'])
                ->whereNumber('ownerId');
            Route::get('/owner/{ownerId}/stats', [ServiceBookingController::class, 'bookingStatsByOwnerId'])
                ->whereNumber('ownerId');    
            Route::get('/{service_booking}', [ServiceBookingController::class, 'show'])
                ->whereNumber('service_booking');
            Route::get('/{ownerId}/refunded-cancelled', [
                ServiceBookingController::class,
                'refundedCancelledBookingsByOwnerId'
            ])->whereNumber('ownerId');

            Route::post('/{bookingId}/owner-cancel-refund',[ServiceBookingController::class, 'ownerCancelAndRefund']);   

            Route::match(['put', 'patch'], '/{service_booking}', [ServiceBookingController::class, 'update'])
                ->whereNumber('service_booking');
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Accounts
        |--------------------------------------------------------------------------
        */

        Route::prefix('payment-accounts')->group(function () {
            Route::get('/', [PaymentAccountController::class, 'index']);

            Route::post('/', [PaymentAccountController::class, 'store']);
            
            Route::get('/check-company/{userId}', [PaymentAccountController::class, 'checkCompanyBankAccount'])
                ->whereNumber('userId');

            Route::get('/{id}', [PaymentAccountController::class, 'show'])
                ->whereNumber('id');

            Route::match(['put', 'patch'], '/{id}', [PaymentAccountController::class, 'update'])
                ->whereNumber('id');

            Route::delete('/{id}', [PaymentAccountController::class, 'destroy'])
                ->whereNumber('id');
        });


     /*
    |--------------------------------------------------------------------------
    | Service Booking Providers
    |--------------------------------------------------------------------------
    */

    Route::prefix('service-booking-providers')
    ->name('owner.service-booking-providers.')
    ->group(function () {
        Route::get('/', [ServiceBookingProviderController::class, 'index'])
            ->name('index');

        Route::post('/', [ServiceBookingProviderController::class, 'store'])
            ->name('store');

        Route::get('/booking/{bookingId}', [ServiceBookingProviderController::class, 'showByBookingId'])
            ->whereNumber('bookingId')
            ->name('by-booking');

        Route::get('/provider/{providerId}', [ServiceBookingProviderController::class, 'showByProviderId'])
            ->whereNumber('providerId')
            ->name('by-provider');

        Route::get('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'show'])
            ->whereNumber('serviceBookingProvider')
            ->name('show');

        Route::put('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'update'])
            ->whereNumber('serviceBookingProvider')
            ->name('update');

        Route::patch('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'update'])
            ->whereNumber('serviceBookingProvider')
            ->name('patch');

        Route::delete('/{serviceBookingProvider}', [ServiceBookingProviderController::class, 'destroy'])
            ->whereNumber('serviceBookingProvider')
            ->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Owner Payouts
    |--------------------------------------------------------------------------
    */

    Route::prefix('payouts')->group(function () {
        // Logged-in owner monthly stats
        Route::get('/stats', [OwnerPayoutController::class, 'stats']);
    
        // Logged-in owner monthly payouts
        Route::get('/', [OwnerPayoutController::class, 'index']);
    
        // Admin/view by owner id
        Route::get('/{ownerId}/stats', [OwnerPayoutController::class, 'statsByOwnerId'])
            ->whereNumber('ownerId');
    
        Route::get('/{ownerId}', [OwnerPayoutController::class, 'showByOwnerId'])
            ->whereNumber('ownerId');
    
        // Show one payout
        Route::get('/{id}', [OwnerPayoutController::class, 'show'])
            ->whereNumber('id');
    });

 });
