<?php

use App\Http\Controllers\Api\Admin\AdminCatalogController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminPeopleController;
use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\Auth\DealerAuthController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\StaffAuthController;
use App\Http\Controllers\Api\Catalog\CategoryCatalogController;
use App\Http\Controllers\Api\Catalog\HomepageCatalogController;
use App\Http\Controllers\Api\Catalog\ProductCatalogController;
use App\Http\Controllers\Api\Catalog\TranslationCatalogController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;
use App\Http\Controllers\Api\Dealer\DealerController;
use App\Http\Controllers\Api\Dealer\DealerOrderController;
use App\Http\Controllers\Api\Salesman\SalesmanAttendanceController;
use App\Http\Controllers\Api\Salesman\SalesmanDashboardController;
use App\Http\Controllers\Api\Salesman\SalesmanFinanceController;
use App\Http\Controllers\Api\Salesman\SalesmanHrController;
use App\Http\Controllers\Api\Salesman\SalesmanOrderController;
use Illuminate\Support\Facades\Route;

$registerBawaskarApi = static function (): void {
    Route::get('health', static fn () => response()->json([
        'success' => true,
        'message' => 'Bawaskar ERP API is running.',
        'data' => ['timestamp' => now()->toIso8601String()],
    ]))->middleware('throttle:api');

    Route::prefix('auth')->group(function (): void {
        Route::post('otp/request', [OtpController::class, 'request'])->middleware('throttle:otp');

        Route::middleware('throttle:login')->group(function (): void {
            Route::post('customer/otp/verify', [CustomerAuthController::class, 'verifyOtp']);
            Route::post('customer/login', [CustomerAuthController::class, 'login']);
            Route::post('customer/register', [CustomerAuthController::class, 'register']);
            Route::post('dealer/otp/verify', [DealerAuthController::class, 'verifyOtp']);
            Route::post('dealer/login', [DealerAuthController::class, 'login']);
            Route::post('salesman/login', [StaffAuthController::class, 'salesmanLogin']);
            Route::post('admin/login', [StaffAuthController::class, 'adminLogin']);
        });

        Route::post('logout', [StaffAuthController::class, 'logout'])->middleware('throttle:api');
    });

    Route::middleware('throttle:api')->group(function (): void {
        Route::get('catalog/categories', [CategoryCatalogController::class, 'index']);
        Route::get('catalog/products', [ProductCatalogController::class, 'index']);
        Route::get('catalog/homepage', [HomepageCatalogController::class, 'index']);
        Route::get('translations', [TranslationCatalogController::class, 'index']);

        Route::prefix('customer')->middleware('api.auth:customer')->group(function (): void {
            Route::get('dashboard', [CustomerController::class, 'dashboard']);
            Route::get('profile', [CustomerController::class, 'profile']);
            Route::post('addresses', [CustomerController::class, 'storeAddress']);
            Route::post('support', [CustomerController::class, 'support']);
            Route::get('orders', [CustomerOrderController::class, 'index']);
            Route::post('orders', [CustomerOrderController::class, 'store']);
            Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
        });

        Route::prefix('dealer')->middleware('api.auth:dealer')->group(function (): void {
            Route::get('dashboard', [DealerController::class, 'dashboard']);
            Route::get('profile', [DealerController::class, 'profile']);
            Route::post('addresses', [DealerController::class, 'storeAddress']);
            Route::post('support', [DealerController::class, 'support']);
            Route::get('statements', [DealerController::class, 'statements']);
            Route::get('orders', [DealerOrderController::class, 'index']);
            Route::post('orders', [DealerOrderController::class, 'store']);
            Route::get('orders/{order}', [DealerOrderController::class, 'show']);
        });

        Route::prefix('salesman')->middleware('api.auth:salesman')->group(function (): void {
            Route::get('dashboard', [SalesmanDashboardController::class, 'dashboard']);
            Route::get('dealers', [SalesmanDashboardController::class, 'dealers']);

            Route::post('attendance/check-in', [SalesmanAttendanceController::class, 'checkIn']);
            Route::post('attendance/check-out', [SalesmanAttendanceController::class, 'checkOut']);
            Route::get('visits', [SalesmanAttendanceController::class, 'visits']);
            Route::post('visits', [SalesmanAttendanceController::class, 'storeVisit']);

            Route::get('orders', [SalesmanOrderController::class, 'index']);
            Route::post('orders', [SalesmanOrderController::class, 'store']);
            Route::post('orders/{order}/forward-to-admin', [SalesmanOrderController::class, 'forwardToAdmin']);
            Route::get('deliveries', [SalesmanOrderController::class, 'deliveries']);

            Route::post('collections', [SalesmanFinanceController::class, 'collectPayment']);
            Route::get('expenses', [SalesmanFinanceController::class, 'expenses']);
            Route::post('expenses', [SalesmanFinanceController::class, 'storeExpense']);
            Route::get('salary', [SalesmanFinanceController::class, 'salary']);
            Route::get('targets', [SalesmanFinanceController::class, 'targets']);

            Route::get('leaves', [SalesmanHrController::class, 'leaves']);
            Route::post('leaves', [SalesmanHrController::class, 'storeLeave']);
            Route::get('assets', [SalesmanHrController::class, 'assets']);
            Route::get('tour-plans', [SalesmanHrController::class, 'tourPlans']);
        });

        Route::prefix('admin')->middleware('api.auth:admin')->group(function (): void {
            Route::get('dashboard', [AdminDashboardController::class, 'dashboard']);

            Route::post('salesmen', [AdminPeopleController::class, 'createSalesman']);
            Route::get('dealers', [AdminPeopleController::class, 'dealers']);
            Route::post('dealers/{dealer}/approve', [AdminPeopleController::class, 'approveDealer']);
            Route::post('dealers/{dealer}/assign', [AdminPeopleController::class, 'assignDealer']);
            Route::post('salesmen/{salesman}/assets', [AdminPeopleController::class, 'assignAsset']);

            Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
            Route::post('orders/{order}/dispatch', [AdminOrderController::class, 'upsertDispatch']);

            Route::post('products', [AdminCatalogController::class, 'storeProduct']);
            Route::post('translations', [AdminCatalogController::class, 'upsertTranslation']);
        });
    });
};

// Versioned endpoints for all new mobile applications.
Route::prefix('v1')->group($registerBawaskarApi);

// Legacy aliases retained so the existing backend/API clients keep working.
$registerBawaskarApi();
