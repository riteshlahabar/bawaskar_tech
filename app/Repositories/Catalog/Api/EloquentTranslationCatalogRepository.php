<?php

namespace App\Repositories\Catalog\Api;

use App\Contracts\Catalog\Api\Repositories\TranslationCatalogRepositoryContract;
use App\Models\Communication\AppTranslation;
use Illuminate\Support\Collection;

final class EloquentTranslationCatalogRepository implements TranslationCatalogRepositoryContract
{
    public function activeForLocale(string $locale): Collection
    {
        return AppTranslation::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->pluck('value', 'translation_key');
    }
}
