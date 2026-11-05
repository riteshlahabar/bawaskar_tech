<?php

namespace App\Services\Catalog;

use App\Contracts\Catalog\BulkProductCategoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;

final class BulkProductCategoryService implements BulkProductCategoryContract
{
    public function move(array $productIds, Category $to): int
    {
        $ids = array_values(array_filter(array_map('intval', $productIds)));

        if ($ids === []) {
            return 0;
        }

        // A plain mass update, like HomepageSectionProductsService::transfer():
        // only the category changes, so none of the product's own data
        // (variants, stock, images, homepage copy) is touched and no model
        // event needs to fire. Products already in the target are excluded so
        // the reported count is the number that actually moved.
        return Product::query()
            ->whereKey($ids)
            ->where('category_id', '!=', $to->getKey())
            ->update(['category_id' => $to->getKey()]);
    }
}
