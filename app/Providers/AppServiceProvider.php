<?php

namespace App\Providers;

use App\Contracts\Admin\FormFieldTreeContract;
use App\Contracts\Admin\FormFieldViewContract;
use App\Contracts\Admin\Imports\ImportFileReaderContract;
use App\Contracts\Admin\Imports\ImportImageArchiveContract;
use App\Contracts\Admin\Imports\ImportImagePathContract;
use App\Contracts\Admin\Imports\ImportRelationResolverContract;
use App\Contracts\Admin\Imports\ImportRowMapperContract;
use App\Contracts\Admin\Imports\ImportRowReaderContract;
use App\Contracts\Admin\Imports\ImportRunnerContract;
use App\Contracts\Admin\Imports\ImportSampleContract;
use App\Contracts\Admin\Imports\ProductImportMastersContract;
use App\Contracts\Admin\Imports\ProductImportMediaContract;
use App\Contracts\Admin\Modules\ModuleDefinitionContract;
use App\Contracts\Admin\Modules\ModuleExportContract;
use App\Contracts\Admin\Modules\ModuleFormDataContract;
use App\Contracts\Admin\Modules\ModuleInputContract;
use App\Contracts\Admin\Modules\ModuleQueryContract;
use App\Contracts\Admin\Modules\ModuleValidationContract;
use App\Contracts\Auth\ApiTokenGuardContract;
use App\Contracts\Auth\OtpContract;
use App\Contracts\Catalog\Product\ProductFormContract;
use App\Contracts\Catalog\Product\ProductImageContract;
use App\Contracts\Catalog\Product\ProductInputContract;
use App\Contracts\Catalog\Product\ProductMediaContract;
use App\Contracts\Catalog\Product\ProductRepositoryContract;
use App\Contracts\Catalog\Product\ProductSkuContract;
use App\Contracts\Catalog\Product\ProductStockContract;
use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Contracts\Catalog\Product\ProductValidationContract;
use App\Contracts\Catalog\Product\ProductVariantContract;
use App\Contracts\Catalog\Product\ProductVariantFormDataContract;
use App\Contracts\Catalog\Product\ProductVariantProjectionContract;
use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Contracts\Catalog\Product\ProductWorkflowContract;
use App\Contracts\Catalog\ProductTranslationRepositoryContract;
use App\Contracts\Catalog\ProductTranslationServiceContract;
use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Files\PublicUploadContract;
use App\Contracts\Sales\Orders\DealerOrderContextContract;
use App\Contracts\Sales\Orders\OrderCheckoutMapperContract;
use App\Contracts\Sales\Orders\OrderLineBuilderContract;
use App\Contracts\Sales\Orders\OrderLineQuantityContract;
use App\Contracts\Sales\Orders\OrderNumberGeneratorContract;
use App\Contracts\Sales\Orders\OrderPricingContract;
use App\Contracts\Sales\Orders\OrderProductResolverContract;
use App\Contracts\Sales\Orders\OrderRepositoryContract;
use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Contracts\Sales\Orders\StockAvailabilityContract;
use App\Contracts\Sales\Orders\StockReservationContract;
use App\Contracts\Support\TransactionManagerContract;
use App\Repositories\Catalog\EloquentProductRepository;
use App\Repositories\Catalog\EloquentProductTranslationRepository;
use App\Repositories\Sales\Orders\EloquentOrderProductResolver;
use App\Repositories\Sales\Orders\EloquentOrderRepository;
use App\Services\Admin\Imports\ImportImagePathNormalizer;
use App\Services\Admin\Imports\ImportRelationResolver;
use App\Services\Admin\Imports\ImportRowMapper;
use App\Services\Admin\Imports\ImportRowReader;
use App\Services\Admin\Imports\ImportSampleBuilder;
use App\Services\Admin\Imports\ModuleImportRunner;
use App\Services\Admin\Imports\ProductImportMasters;
use App\Services\Admin\Imports\ProductImportMedia;
use App\Services\Admin\Imports\SpreadsheetImportReader;
use App\Services\Admin\Imports\ZipImportImageExtractor;
use App\Services\Admin\Modules\ModuleDefinition;
use App\Services\Admin\Modules\ModuleExport;
use App\Services\Admin\Modules\ModuleFormData;
use App\Services\Admin\Modules\ModuleInput;
use App\Services\Admin\Modules\ModuleQuery;
use App\Services\Admin\Modules\ModuleValidation;
use App\Services\Auth\ApiTokenGuard;
use App\Services\Auth\OtpService;
use App\Services\Catalog\GoogleTextTranslator;
use App\Services\Catalog\Product\ProductFormService;
use App\Services\Catalog\Product\ProductImageService;
use App\Services\Catalog\Product\ProductInputService;
use App\Services\Catalog\Product\ProductMediaService;
use App\Services\Catalog\Product\ProductSkuService;
use App\Services\Catalog\Product\ProductStockService;
use App\Services\Catalog\Product\ProductValidationService;
use App\Services\Catalog\Product\ProductVariantFormDataService;
use App\Services\Catalog\Product\ProductVariantProjectionService;
use App\Services\Catalog\Product\ProductVariantService;
use App\Services\Catalog\Product\ProductVariantUnitService;
use App\Services\Catalog\Product\ProductWorkflowService;
use App\Services\Catalog\ProductTranslationService;
use App\Services\Files\PublicUploadService;
use App\Services\Sales\Orders\DealerOrderContextService;
use App\Services\Sales\Orders\EloquentStockAvailabilityService;
use App\Services\Sales\Orders\EloquentStockReservationService;
use App\Services\Sales\Orders\OrderCheckoutMapper;
use App\Services\Sales\Orders\OrderLineBuilderService;
use App\Services\Sales\Orders\OrderLineQuantityService;
use App\Services\Sales\Orders\OrderPricingService;
use App\Services\Sales\Orders\OrderWorkflowService;
use App\Services\Sales\Orders\TimestampOrderNumberGenerator;
use App\Services\Support\LaravelTransactionManager;
use App\Support\Admin\Forms\ConfigFormFieldViews;
use App\Support\Admin\Forms\FormFieldTree;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $bindings = [
            ProductValidationContract::class => ProductValidationService::class,
            ProductInputContract::class => ProductInputService::class,
            ProductWorkflowContract::class => ProductWorkflowService::class,
            ProductFormContract::class => ProductFormService::class,
            ProductImageContract::class => ProductImageService::class,
            ProductVariantContract::class => ProductVariantService::class,
            ProductVariantFormDataContract::class => ProductVariantFormDataService::class,
            ProductVariantUnitContract::class => ProductVariantUnitService::class,
            ProductSkuContract::class => ProductSkuService::class,
            ProductVariantProjectionContract::class => ProductVariantProjectionService::class,
            ProductMediaContract::class => ProductMediaService::class,
            ProductStockContract::class => ProductStockService::class,
            ProductTranslationContract::class => \App\Services\Catalog\Product\ProductTranslationService::class,
            \App\Contracts\Catalog\Product\TextTranslatorContract::class => \App\Services\Catalog\Product\GoogleTextTranslator::class,
            ProductRepositoryContract::class => EloquentProductRepository::class,
            PublicUploadContract::class => PublicUploadService::class,
            ApiTokenGuardContract::class => ApiTokenGuard::class,
            OtpContract::class => OtpService::class,
            ModuleDefinitionContract::class => ModuleDefinition::class,
            ModuleQueryContract::class => ModuleQuery::class,
            ModuleValidationContract::class => ModuleValidation::class,
            ModuleFormDataContract::class => ModuleFormData::class,
            ModuleInputContract::class => ModuleInput::class,
            ModuleExportContract::class => ModuleExport::class,
            ImportRowReaderContract::class => ImportRowReader::class,
            ImportFileReaderContract::class => SpreadsheetImportReader::class,
            ImportImagePathContract::class => ImportImagePathNormalizer::class,
            ImportImageArchiveContract::class => ZipImportImageExtractor::class,
            ImportRowMapperContract::class => ImportRowMapper::class,
            ImportRelationResolverContract::class => ImportRelationResolver::class,
            ProductImportMastersContract::class => ProductImportMasters::class,
            ProductImportMediaContract::class => ProductImportMedia::class,
            ImportSampleContract::class => ImportSampleBuilder::class,
            ImportRunnerContract::class => ModuleImportRunner::class,
            FormFieldTreeContract::class => FormFieldTree::class,
            FormFieldViewContract::class => ConfigFormFieldViews::class,

            // Order module - SOLID contracts
            OrderWorkflowContract::class => OrderWorkflowService::class,
            OrderLineBuilderContract::class => OrderLineBuilderService::class,
            OrderLineQuantityContract::class => OrderLineQuantityService::class,
            OrderPricingContract::class => OrderPricingService::class,
            StockAvailabilityContract::class => EloquentStockAvailabilityService::class,
            StockReservationContract::class => EloquentStockReservationService::class,
            OrderNumberGeneratorContract::class => TimestampOrderNumberGenerator::class,
            OrderRepositoryContract::class => EloquentOrderRepository::class,
            OrderProductResolverContract::class => EloquentOrderProductResolver::class,
            DealerOrderContextContract::class => DealerOrderContextService::class,
            OrderCheckoutMapperContract::class => OrderCheckoutMapper::class,
            TransactionManagerContract::class => LaravelTransactionManager::class,
        ];

        foreach ($bindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }        $this->app->bind(
            TextTranslatorContract::class,
            GoogleTextTranslator::class
        );

        $this->app->bind(
            ProductTranslationRepositoryContract::class,
            EloquentProductTranslationRepository::class
        );

        $this->app->bind(
            ProductTranslationServiceContract::class,
            ProductTranslationService::class
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
        View::composer('admin.shared.form', function ($view): void {
            $tree = app(FormFieldTreeContract::class);
            $module = $view->getData()['module'] ?? [];

            $view->with([
                'fieldTree' => $tree,
                'fieldViews' => app(FormFieldViewContract::class),
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
