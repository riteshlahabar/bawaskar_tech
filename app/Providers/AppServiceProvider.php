<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $bindings = [
            \App\Contracts\Catalog\Product\ProductValidationContract::class => \App\Services\Catalog\Product\ProductValidationService::class,
            \App\Contracts\Catalog\Product\ProductInputContract::class => \App\Services\Catalog\Product\ProductInputService::class,
            \App\Contracts\Catalog\Product\ProductWorkflowContract::class => \App\Services\Catalog\Product\ProductWorkflowService::class,
            \App\Contracts\Catalog\Product\ProductFormContract::class => \App\Services\Catalog\Product\ProductFormService::class,
            \App\Contracts\Catalog\Product\ProductImageContract::class => \App\Services\Catalog\Product\ProductImageService::class,
            \App\Contracts\Catalog\Product\ProductVariantContract::class => \App\Services\Catalog\Product\ProductVariantService::class,
            \App\Contracts\Catalog\Product\ProductVariantFormDataContract::class => \App\Services\Catalog\Product\ProductVariantFormDataService::class,
            \App\Contracts\Catalog\Product\ProductVariantUnitContract::class => \App\Services\Catalog\Product\ProductVariantUnitService::class,
            \App\Contracts\Catalog\Product\ProductSkuContract::class => \App\Services\Catalog\Product\ProductSkuService::class,
            \App\Contracts\Catalog\Product\ProductVariantProjectionContract::class => \App\Services\Catalog\Product\ProductVariantProjectionService::class,
            \App\Contracts\Catalog\Product\ProductMediaContract::class => \App\Services\Catalog\Product\ProductMediaService::class,
            \App\Contracts\Catalog\Product\ProductStockContract::class => \App\Services\Catalog\Product\ProductStockService::class,
            \App\Contracts\Catalog\Product\ProductTranslationContract::class => \App\Services\Catalog\Product\ProductTranslationService::class,
            \App\Contracts\Catalog\Product\TextTranslatorContract::class => \App\Services\Catalog\Product\GoogleTextTranslator::class,
            \App\Contracts\Catalog\Product\ProductRepositoryContract::class => \App\Repositories\Catalog\EloquentProductRepository::class,
            \App\Contracts\Files\PublicUploadContract::class => \App\Services\Files\PublicUploadService::class,
            \App\Contracts\Auth\ApiTokenGuardContract::class => \App\Services\Auth\ApiTokenGuard::class,
            \App\Contracts\Admin\FormFieldTreeContract::class => \App\Support\Admin\Forms\FormFieldTree::class,
            \App\Contracts\Admin\FormFieldViewContract::class => \App\Support\Admin\Forms\ConfigFormFieldViews::class,

            // Order module - SOLID contracts
            \App\Contracts\Sales\Orders\OrderWorkflowContract::class => \App\Services\Sales\Orders\OrderWorkflowService::class,
            \App\Contracts\Sales\Orders\OrderLineBuilderContract::class => \App\Services\Sales\Orders\OrderLineBuilderService::class,
            \App\Contracts\Sales\Orders\OrderLineQuantityContract::class => \App\Services\Sales\Orders\OrderLineQuantityService::class,
            \App\Contracts\Sales\Orders\OrderPricingContract::class => \App\Services\Sales\Orders\OrderPricingService::class,
            \App\Contracts\Sales\Orders\StockAvailabilityContract::class => \App\Services\Sales\Orders\EloquentStockAvailabilityService::class,
            \App\Contracts\Sales\Orders\StockReservationContract::class => \App\Services\Sales\Orders\EloquentStockReservationService::class,
            \App\Contracts\Sales\Orders\OrderNumberGeneratorContract::class => \App\Services\Sales\Orders\TimestampOrderNumberGenerator::class,
            \App\Contracts\Sales\Orders\OrderRepositoryContract::class => \App\Repositories\Sales\Orders\EloquentOrderRepository::class,
            \App\Contracts\Sales\Orders\OrderProductResolverContract::class => \App\Repositories\Sales\Orders\EloquentOrderProductResolver::class,
            \App\Contracts\Sales\Orders\DealerOrderContextContract::class => \App\Services\Sales\Orders\DealerOrderContextService::class,
            \App\Contracts\Sales\Orders\OrderCheckoutMapperContract::class => \App\Services\Sales\Orders\OrderCheckoutMapper::class,
            \App\Contracts\Support\TransactionManagerContract::class => \App\Services\Support\LaravelTransactionManager::class,
        ];

        foreach ($bindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }        $this->app->bind(
            \App\Contracts\Catalog\TextTranslatorContract::class,
            \App\Services\Catalog\GoogleTextTranslator::class
        );

        $this->app->bind(
            \App\Contracts\Catalog\ProductTranslationRepositoryContract::class,
            \App\Repositories\Catalog\EloquentProductTranslationRepository::class
        );

        $this->app->bind(
            \App\Contracts\Catalog\ProductTranslationServiceContract::class,
            \App\Services\Catalog\ProductTranslationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // The shared admin form and its field partials get the layout services
        // handed to them, so the Blade templates hold no static calls.
        \Illuminate\Support\Facades\View::composer('admin.shared.form', function ($view): void {
            $tree = app(\App\Contracts\Admin\FormFieldTreeContract::class);
            $module = $view->getData()['module'] ?? [];

            $view->with([
                'fieldTree' => $tree,
                'fieldViews' => app(\App\Contracts\Admin\FormFieldViewContract::class),
                'fieldNodes' => $tree->build($module['fields'] ?? []),
            ]);
        });
        RateLimiter::for('api', function (Request $request): Limit {
            $key = $request->bearerToken()
                ? 'token:'.hash('sha256', $request->bearerToken())
                : 'ip:'.$request->ip();

            return Limit::perMinute((int) env('API_RATE_LIMIT_PER_MINUTE', 120))->by($key);
        });

        RateLimiter::for('otp', function (Request $request): Limit {
            $mobile = preg_replace('/\D+/', '', (string) $request->input('mobile')) ?: 'unknown';

            return Limit::perMinute((int) env('OTP_RATE_LIMIT_PER_MINUTE', 5))->by($mobile.'|'.$request->ip());
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $identifier = strtolower((string) ($request->input('email') ?: $request->input('mobile') ?: 'unknown'));

            return Limit::perMinute((int) env('LOGIN_RATE_LIMIT_PER_MINUTE', 10))->by($identifier.'|'.$request->ip());
        });
    }
}