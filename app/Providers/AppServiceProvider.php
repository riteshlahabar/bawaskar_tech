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
            \App\Contracts\Catalog\Product\ProductMediaContract::class => \App\Services\Catalog\Product\ProductMediaService::class,
            \App\Contracts\Catalog\Product\ProductStockContract::class => \App\Services\Catalog\Product\ProductStockService::class,
            \App\Contracts\Catalog\Product\ProductTranslationContract::class => \App\Services\Catalog\Product\ProductTranslationService::class,
            \App\Contracts\Catalog\Product\TextTranslatorContract::class => \App\Services\Catalog\Product\GoogleTextTranslator::class,
            \App\Contracts\Catalog\Product\ProductRepositoryContract::class => \App\Repositories\Catalog\EloquentProductRepository::class,
            \App\Contracts\Files\PublicUploadContract::class => \App\Services\Files\PublicUploadService::class,
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