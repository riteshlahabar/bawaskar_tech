<?php

namespace App\Contracts\Catalog\Api\Presenters;

use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;

interface HomepageCatalogPresenterContract
{
    public function item(ProductHomepageSectionItem $item): array;

    public function fallbackBanner(StorefrontBanner $banner): array;

    public function legacyBanner(StorefrontBanner $banner): array;
}
