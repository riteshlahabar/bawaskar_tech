<?php

namespace App\Contracts\Storefront\Repositories;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontSection;
use Illuminate\Support\Collection;

interface StorefrontHomepageRepositoryContract
{
    public function homepageSections(): Collection;

    public function legacySections(): Collection;

    public function legacyBanners(): Collection;

    public function serviceBlocks(): Collection;

    public function footerLinks(): Collection;

    public function productsForLegacySection(StorefrontSection $section, int $limit, string $audience): Collection;

    public function productsForHomepageSection(ProductHomepageSection $section, int $limit, string $audience): Collection;

    public function topSellingProducts(string $audience): Collection;

    public function dealTimerProduct(string $audience): ?Product;

    /**
     * One deal product per Top Selling row - its first content row - for the
     * header "Deal Today" popup.
     */
    public function dealProducts(string $audience, int $limit): Collection;
}
