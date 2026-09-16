<?php

namespace App\Services\Localization;

use App\Contracts\Localization\SupportedLocalesContract;
use App\Models\Communication\Language;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * SRP: read the admin-managed Languages list, with a config fallback.
 *
 * The list is cached because it is consulted on every API request; the admin
 * Languages module bumps that cache when a language is added or disabled.
 */
final class DatabaseSupportedLocalesService implements SupportedLocalesContract
{
    private const CACHE_KEY = 'supported_locales';

    private const CACHE_MINUTES = 60;

    public function codes(): array
    {
        try {
            $codes = Cache::remember(
                self::CACHE_KEY,
                now()->addMinutes(self::CACHE_MINUTES),
                fn (): array => Language::query()
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->pluck('code')
                    ->all()
            );
        } catch (Throwable) {
            // Migrations not run yet, or the database is unreachable.
            $codes = [];
        }

        return $codes === [] ? $this->fallbackCodes() : $codes;
    }

    public function translatable(): array
    {
        $default = $this->default();

        return array_values(array_filter(
            $this->codes(),
            static fn (string $code): bool => $code !== $default
        ));
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->codes(), true);
    }

    public function default(): string
    {
        return (string) config('app.locale', 'en');
    }

    /**
     * @return array<int, string>
     */
    private function fallbackCodes(): array
    {
        return (array) config('localization.fallback_locales', ['en']);
    }
}
