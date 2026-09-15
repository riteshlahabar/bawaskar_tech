<?php

namespace App\Contracts\Catalog\Api\Presenters;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;

interface HomepageCatalogPresenterContract
{
    public function item(ProductHomepageSectionItem $item): array;

    /**
     * A product configured as a homepage banner / offer entry, in the same
     * shape as item() so the apps render both the same way.
     */
    public function productEntry(Product $product): array;

    public function fallbackBanner(StorefrontBanner $banner): array;

    public function legacyBanner(StorefrontBanner $banner): array;
}
