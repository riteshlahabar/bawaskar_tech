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
        //
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