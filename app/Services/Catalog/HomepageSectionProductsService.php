<?php

namespace App\Services\Catalog;

use App\Contracts\Catalog\HomepageSectionProductsContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use Illuminate\Support\Collection;

final class HomepageSectionProductsService implements HomepageSectionProductsContract
{
    public function attachedCount(ProductHomepageSection $section): int
    {
        return $section->products()->count();
    }

    /**
     * Only rows a product can really be placed on. Transferring onto a content
     * row or onto Top Selling would set `homepage_section_id` on a row that
     * never reads it, so the products would silently disappear instead of
     * moving (item 48).
     */
    public function transferTargets(ProductHomepageSection $section): Collection
    {
        return ProductHomepageSection::query()
            ->whereKeyNot($section->getKey())
            ->whereIn('section_type', ProductHomepageSection::ASSIGNABLE_SECTION_TYPES)
            ->orderBy('title')
            ->get(['id', 'title', 'section_type']);
    }

    public function blockedTitles(array $sectionIds): array
    {
        $ids = array_filter(array_map('intval', $sectionIds));

        if ($ids === []) {
            return [];
        }

        return ProductHomepageSection::query()
            ->whereKey($ids)
            ->whereHas('products')
            ->orderBy('title')
            ->pluck('title')
            ->all();
    }

    public function transfer(ProductHomepageSection $from, ProductHomepageSection $to): int
    {
        // A plain mass update: only the placement changes, so none of the
        // product's own homepage copy (title, image, button) is touched and no
        // model event needs to fire.
        return Product::query()
            ->where('homepage_section_id', $from->getKey())
            ->update(['homepage_section_id' => $to->getKey()]);
    }
}
