<?php

use App\Models\Communication\WebTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

if (! function_exists('web_t')) {
    function web_t(string $key, ?string $englishText = null, array $replace = [], ?string $locale = null): string
    {
        $fallback = $englishText ?? Str::of($key)->afterLast('.')->replace('_', ' ')->headline()->toString();
        $locale = $locale ?: app()->getLocale();

        if ($locale === '' || $locale === 'en') {
            return strtr($fallback, $replace);
        }

        $cacheVersion = (int) Cache::get('catalog_cache_version', 1);
        $translations = Cache::remember(
            'web_translations.'.$cacheVersion.'.'.$locale,
            now()->addHours(6),
            fn () => WebTranslation::query()
                ->where('locale', $locale)
                ->where('is_active', true)
                ->pluck('value', 'translation_key')
                ->all()
        );

        $value = (string) ($translations[$key] ?? $fallback);

        return strtr($value !== '' ? $value : $fallback, $replace);
    }
}
