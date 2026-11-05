<?php

namespace App\Contracts\Catalog;

use App\Models\Catalog\ProductHomepageSection;

/**
 * Moving several products onto one homepage section in a single step.
 *
 * The sibling of BulkProductCategoryContract, kept separate because the two
 * columns behave differently: `products.homepage_section_id` is genuinely
 * optional, so a null target is a real instruction ("take these products off
 * the homepage") rather than a missing value.
 *
 * HomepageSectionProductsContract::transfer() already moves *every* product off
 * one row onto another for the row's own delete guard; this moves only the
 * products ticked on the Products listing.
 */
interface BulkProductSectionContract
{
    /**
     * Places every given product on the target section, or clears the
     * placement when the target is null.
     *
     * @param  array<int, int|string>  $productIds
     * @return int Number of products actually moved.
     */
    public function move(array $productIds, ?ProductHomepageSection $to): int;
}
