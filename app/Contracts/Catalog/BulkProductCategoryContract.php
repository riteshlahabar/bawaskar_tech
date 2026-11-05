<?php

namespace App\Contracts\Catalog;

use App\Models\Catalog\Category;

/**
 * Moving several products into one category in a single step.
 *
 * `products.category_id` is a single required column, so this is a move and
 * never an addition: a product belongs to exactly one category before and
 * after. Done one product at a time it means reopening the full Products form
 * (which forces at least one variant) for every row.
 */
interface BulkProductCategoryContract
{
    /**
     * Puts every given product into the target category.
     *
     * @param  array<int, int|string>  $productIds
     * @return int Number of products actually moved.
     */
    public function move(array $productIds, Category $to): int;
}
