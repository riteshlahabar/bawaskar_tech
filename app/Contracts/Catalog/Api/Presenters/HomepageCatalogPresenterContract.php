<?php

namespace App\Contracts\Catalog\Api\Presenters;

use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;

interface HomepageCatalogPresenterContract
{
    /**
     * One homepage content row. `productEntry()` used to present a product
     * built as a banner; a banner is a content row now and carries the product
     * it links to in `product_id`, so this is the only entry shape left.
     */
    public function item(ProductHomepageSectionItem $item): array;

    public function fallbackBanner(StorefrontBanner $banner): array;

    public function legacyBanner(StorefrontBanner $banner): array;
}
