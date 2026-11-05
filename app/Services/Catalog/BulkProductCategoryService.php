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
        // event needs to fire.
        //
        // The update is NOT narrowed with `where('category_id', '!=', $to)`:
        // the column is nullable, and in SQL a NULL never satisfies `!=`, so
        // a product sitting in no category at all would be skipped forever.
        // The count of products that actually change is read first instead.
        $moving = Product::query()
            ->whereKey($ids)
            ->where(function ($query) use ($to) {
                $query->whereNull('category_id')
                    ->orWhere('category_id', '!=', $to->getKey());
            })
            ->count();

        Product::query()
            ->whereKey($ids)
            ->update(['category_id' => $to->getKey()]);

        return $moving;
    }
}
