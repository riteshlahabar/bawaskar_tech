<?php

namespace App\Contracts\Catalog;

use App\Models\Catalog\ProductHomepageSection;
use Illuminate\Support\Collection;

/**
 * The rule that a homepage row cannot be removed while products still sit on
 * it: admin must move those products to another row, or delete them, first.
 *
 * The database alone would not stop it - `products.homepage_section_id` is
 * `nullOnDelete`, so deleting the row silently unlinks every product and they
 * vanish from the homepage with no record of where they used to be.
 */
interface HomepageSectionProductsContract
{
    /** How many products are currently placed on this row. */
    public function attachedCount(ProductHomepageSection $section): int;

    /**
     * Rows these products could be moved to: every other section, newest
     * titles last, for the transfer dropdown.
     *
     * @return Collection<int, ProductHomepageSection>
     */
    public function transferTargets(ProductHomepageSection $section): Collection;

    /**
     * Titles of the given rows that still hold products, so a bulk delete can
     * name them instead of failing silently.
     *
     * @param  array<int, int|string>  $sectionIds
     * @return array<int, string>
     */
    public function blockedTitles(array $sectionIds): array;

    /** Moves every product off one row onto another. Returns the count moved. */
    public function transfer(ProductHomepageSection $from, ProductHomepageSection $to): int;
}
