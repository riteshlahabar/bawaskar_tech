<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;
use App\Http\Controllers\Api\Dealer\DealerController;
use App\Http\Controllers\Api\Dealer\DealerOrderController;
use App\Http\Controllers\Api\Salesman\SalesmanController;
use Illuminate\Support\Facades\Route;

$registerBawaskarApi = static function (): void {
    Route::get('health', static fn () => response()->json([
        'success' => true,
        'message' => 'Bawaskar ERP API is running.',
        'data' => ['timestamp' => now()->toIso8601String()],
    ]))->middleware('throttle:api');

    Route::prefix('auth')->group(function (): void {
        Route::post('otp/request', [AuthController::class, 'requestOtp'])->middleware('throttle:otp');
        Route::post('customer/otp/verify', [AuthController::class, 'verifyCustomerOtp'])->middleware('throttle:login');
        Route::post('customer/login', [AuthController::class, 'customerLogin'])->middleware('throttle:login');
        Route::post('customer/register', [AuthController::class, 'registerCustomer'])->middleware('throttle:login');
        Route::post('dealer/otp/verify', [AuthController::class, 'verifyDealerOtp'])->middleware('throttle:login');
        Route::post('dealer/login', [AuthController::class, 'dealerLogin'])->middleware('throttle:login');
        Route::post('salesman/login', [AuthController::class, 'salesmanLogin'])->middleware('throttle:login');
        Route::post('admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:login');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('throttle:api');
    });

    Route::middleware('throttle:api')->group(function (): void {
        Route::get('catalog/categories', [CatalogController::class, 'categories']);
        Route::get('catalog/products', [CatalogController::class, 'products']);
        Route::get('catalog/homepage', [CatalogController::class, 'homepage']);
        Route::get('translations', [CatalogController::class, 'translations']);

        Route::prefix('customer')->group(function (): void {
            Route::get('dashboard', [CustomerController::class, 'dashboard']);
            Route::get('profile', [CustomerController::class, 'profile']);
            Route::post('addresses', [CustomerController::class, 'storeAddress']);
            Route::post('support', [CustomerController::class, 'support']);
            Route::get('orders', [CustomerOrderController::class, 'index']);
            Route::post('orders', [CustomerOrderController::class, 'store']);
            Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
        });

        Route::prefix('dealer')->group(function (): void {
            Route::get('dashboard', [DealerController::class, 'dashboard']);
            Route::get('profile', [DealerController::class, 'profile']);
            Route::post('addresses', [DealerController::class, 'storeAddress']);
            Route::post('support', [DealerController::class, 'support']);
            Route::get('statements', [DealerController::class, 'statements']);
            Route::get('orders', [DealerOrderController::class, 'index']);
            Route::post('orders', [DealerOrderController::class, 'store']);
            Route::get('orders/{order}', [DealerOrderController::class, 'show']);
        });

        Route::prefix('salesman')->group(function (): void {
            Route::get('dashboard', [SalesmanController::class, 'dashboard']);
            Route::get('dealers', [SalesmanController::class, 'dealers']);
            Route::post('attendance/check-in', [SalesmanController::class, 'checkIn']);
            Route::post('attendance/check-out', [SalesmanController::class, 'checkOut']);
            Route::get('visits', [SalesmanController::class, 'visits']);
            Route::post('visits', [SalesmanController::class, 'storeVisit']);
            Route::get('orders', [SalesmanController::class, 'orders']);
            Route::post('orders', [SalesmanController::class, 'storeDealerOrder']);
            Route::post('orders/{order}/forward-to-admin', [SalesmanController::class, 'forwardOrderToAdmin']);
            Route::post('collections', [SalesmanController::class, 'collectPayment']);
            Route::get('expenses', [SalesmanController::class, 'expenses']);
            Route::post('expenses', [SalesmanController::class, 'storeExpense']);
            Route::get('leaves', [SalesmanController::class, 'leaves']);
            Route::post('leaves', [SalesmanController::class, 'storeLeave']);
            Route::get('assets', [SalesmanController::class, 'assets']);
            Route::get('salary', [SalesmanController::class, 'salary']);
            Route::get('targets', [SalesmanController::class, 'targets']);
            Route::get('tour-plans', [SalesmanController::class, 'tourPlans']);
            Route::get('deliveries', [SalesmanController::class, 'deliveries']);
        });

        Route::prefix('admin')->group(function (): void {
            Route::get('dashboard', [AdminController::class, 'dashboard']);
            Route::post('salesmen', [AdminController::class, 'createSalesman']);
            Route::get('dealers', [AdminController::class, 'dealers']);
            Route::post('dealers/{dealer}/approve', [AdminController::class, 'approveDealer']);
            Route::post('dealers/{dealer}/assign', [AdminController::class, 'assignDealer']);
            Route::post('products', [AdminController::class, 'storeProduct']);
            Route::post('orders/{order}/status', [AdminController::class, 'updateOrderStatus']);
            Route::post('orders/{order}/dispatch', [AdminController::class, 'upsertDispatch']);
            Route::post('salesmen/{salesman}/assets', [AdminController::class, 'assignAsset']);
            Route::post('translations', [AdminController::class, 'upsertTranslation']);
        });
    });
};

// Versioned endpoints for all new mobile applications.
Route::prefix('v1')->group($registerBawaskarApi);

// Legacy aliases retained so the existing backend/API clients keep working.
$registerBawaskarApi();