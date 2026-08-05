<?php

use App\Http\Controllers\Admin\Assets\AssetController;
use App\Http\Controllers\Admin\Attendance\AttendanceController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Batches\BatchController;
use App\Http\Controllers\Admin\Brands\BrandController;
use App\Http\Controllers\Admin\Categories\CategoryController;
use App\Http\Controllers\Admin\Collections\CollectionController;
use App\Http\Controllers\Admin\Customers\CustomerController;
use App\Http\Controllers\Admin\Couriers\CourierController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Dealers\DealerController;
use App\Http\Controllers\Admin\DealerVisits\DealerVisitController;
use App\Http\Controllers\Admin\Dispatches\DispatchController;
use App\Http\Controllers\Admin\Expenses\ExpenseController;
use App\Http\Controllers\Admin\EmailTemplates\EmailTemplateController;
use App\Http\Controllers\Admin\InternalExpenses\InternalExpenseController;
use App\Http\Controllers\Admin\ExpenseSubcategories\ExpenseSubcategoryController;
use App\Http\Controllers\Admin\ExpenseCategories\ExpenseCategoryController;
use App\Http\Controllers\Admin\Inventory\InventoryController;
use App\Http\Controllers\Admin\Invoices\InvoiceController;
use App\Http\Controllers\Admin\Languages\LanguageController;
use App\Http\Controllers\Admin\Leaves\LeaveController;
use App\Http\Controllers\Admin\Notifications\NotificationController;
use App\Http\Controllers\Admin\Orders\OrderController;
use App\Http\Controllers\Admin\Outstanding\OutstandingController;
use App\Http\Controllers\Admin\Payments\PaymentController;
use App\Http\Controllers\Admin\Pricing\PricingController;
use App\Http\Controllers\Admin\ProformaInvoices\ProformaInvoiceController;
use App\Http\Controllers\Admin\Products\ProductController;
use App\Http\Controllers\Admin\Reports\ReportController;
use App\Http\Controllers\Admin\Returns\ReturnController;
use App\Http\Controllers\Admin\Salary\SalaryController;
use App\Http\Controllers\Admin\SalesDocuments\SalesDocumentController;
use App\Http\Controllers\Admin\Salesmen\SalesmanController;
use App\Http\Controllers\Admin\Support\SupportController;
use App\Http\Controllers\Admin\Targets\TargetController;
use App\Http\Controllers\Admin\TourPlans\TourPlanController;
use App\Http\Controllers\Admin\Translations\TranslationController;
use App\Http\Controllers\Admin\Units\UnitController;
use App\Http\Controllers\Admin\Warehouses\WarehouseController;
use App\Http\Controllers\Admin\StorefrontBanners\StorefrontBannerController;
use App\Http\Controllers\Admin\StorefrontFooterLinks\StorefrontFooterLinkController;
use App\Http\Controllers\Admin\StorefrontSectionProducts\StorefrontSectionProductController;
use App\Http\Controllers\Admin\StorefrontSections\StorefrontSectionController;
use App\Http\Controllers\Admin\StorefrontServiceBlocks\StorefrontServiceBlockController;
use App\Http\Controllers\Admin\StorefrontRows\StorefrontRowController;
use App\Http\Controllers\Admin\Imports\CommonImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('common-import/{module}/sample', [CommonImportController::class, 'sample'])->name('common-import.sample');
        Route::post('common-import/{module}', [CommonImportController::class, 'store'])->name('common-import.store');
        Route::get('/', [DashboardController::class, 'erp'])->name('dashboard');
        Route::get('dashboard/hrms', [DashboardController::class, 'hrms'])->name('dashboard.hrms');
        Route::get('storefront-rows/{row}', [StorefrontRowController::class, 'show'])->name('storefront-rows.show');

        $resources = [
            'dealers'=>DealerController::class,'customers'=>CustomerController::class,'salesmen'=>SalesmanController::class,'couriers'=>CourierController::class,
            'products'=>ProductController::class,'categories'=>CategoryController::class,'brands'=>BrandController::class,'units'=>UnitController::class,'pricing'=>PricingController::class,
            'inventory'=>InventoryController::class,'warehouses'=>WarehouseController::class,'batches'=>BatchController::class,
            'orders'=>OrderController::class,'proforma-invoices'=>ProformaInvoiceController::class,'invoices'=>InvoiceController::class,'dispatches'=>DispatchController::class,'returns'=>ReturnController::class,
            'payments'=>PaymentController::class,'collections'=>CollectionController::class,'outstanding'=>OutstandingController::class,
            'internal-expenses'=>InternalExpenseController::class,'expense-categories'=>ExpenseCategoryController::class,'expense-subcategories'=>ExpenseSubcategoryController::class,
            'attendance'=>AttendanceController::class,'dealer-visits'=>DealerVisitController::class,'tour-plans'=>TourPlanController::class,
            'expenses'=>ExpenseController::class,'leaves'=>LeaveController::class,'salary'=>SalaryController::class,'targets'=>TargetController::class,'assets'=>AssetController::class,
            'storefront-banners'=>StorefrontBannerController::class,'storefront-sections'=>StorefrontSectionController::class,'storefront-section-products'=>StorefrontSectionProductController::class,'storefront-service-blocks'=>StorefrontServiceBlockController::class,'storefront-footer-links'=>StorefrontFooterLinkController::class,
            'notifications'=>NotificationController::class,'languages'=>LanguageController::class,'translations'=>TranslationController::class,'support'=>SupportController::class,
        ];
        Route::get('attendance/bulk', [AttendanceController::class, 'bulk'])->name('attendance.bulk');
        Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore'])->name('attendance.bulk.store');
        foreach ($resources as $uri => $controller) {
            Route::get($uri.'/export/{format}', [$controller, 'export'])->whereIn('format', ['excel', 'pdf'])->name($uri.'.export');
            Route::delete($uri.'/bulk-destroy', [$controller, 'bulkDestroy'])->name($uri.'.bulk-destroy');
            Route::resource($uri, $controller);
        }

        Route::post('dealers/{dealer}/approve', [DealerController::class, 'approve'])->name('dealers.approve');
        Route::post('orders/{id}/convert-to-proforma', [OrderController::class, 'convertToProforma'])->name('orders.convert-to-proforma');
        Route::post('orders/{order}/change-status', [OrderController::class, 'changeStatus'])->name('orders.change-status');
        Route::post('proforma-invoices/{id}/convert-to-invoice', [ProformaInvoiceController::class, 'convertToInvoice'])->name('proforma-invoices.convert-to-invoice');
        Route::get('sales-documents/{document}/{id}/print', [SalesDocumentController::class, 'print'])->whereIn('document', ['order', 'proforma', 'invoice'])->name('sales-documents.print');
        Route::get('sales-documents/{document}/{id}/pdf', [SalesDocumentController::class, 'pdf'])->whereIn('document', ['order', 'proforma', 'invoice'])->name('sales-documents.pdf');
        Route::post('expenses/{expense}/decision', [ExpenseController::class, 'decision'])->name('expenses.decision');
        Route::post('leaves/{leave}/decision', [LeaveController::class, 'decision'])->name('leaves.decision');
        Route::post('salary/generate', [SalaryController::class, 'generate'])->name('salary.generate');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('email-templates/{template}', [EmailTemplateController::class, 'show'])->name('email-templates.show');
    });
});

