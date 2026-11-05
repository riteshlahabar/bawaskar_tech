<?php

namespace App\Services\Catalog;

use App\Contracts\Catalog\BulkProductSectionContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;

final class BulkProductSectionService implements BulkProductSectionContract
{
    public function move(array $productIds, ?ProductHomepageSection $to): int
    {
        $ids = array_values(array_filter(array_map('intval', $productIds)));

        if ($ids === []) {
            return 0;
        }

        $target = $to?->getKey();

        // Count what will really change before writing, then update
        // unconditionally — never put the comparison inside the UPDATE. The
        // column is nullable, and SQL `!=` never matches NULL, so a narrowed
        // UPDATE would silently skip every product that has no section yet
        // (and write nothing at all when it matched nothing).
        $moving = Product::query()
            ->whereKey($ids)
            ->where(function ($query) use ($target) {
                $target === null
                    ? $query->whereNotNull('homepage_section_id')
                    : $query->whereNull('homepage_section_id')
                        ->orWhere('homepage_section_id', '!=', $target);
            })
            ->count();

        // Only the placement changes, so the product's own homepage copy
        // (title, image, button) is left exactly as it is.
        Product::query()
            ->whereKey($ids)
            ->update(['homepage_section_id' => $target]);

        return $moving;
    }
}
