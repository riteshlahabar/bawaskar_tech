<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Contracts\Storefront\StorefrontNavigationContract;

final class StorefrontNavigationService implements StorefrontNavigationContract
{
    /** The Fastkart "Deal Today" popup is laid out for four products. */
    private const DEAL_LIMIT = 4;

    public function __construct(
        private readonly StorefrontNavigationRepositoryContract $navigation,
        // The header popup shows the same deals the homepage does, and a deal
        // is now the first content row of a Top Selling section rather than a
        // product flag, so it is the homepage repository that knows them.
        private readonly StorefrontHomepageRepositoryContract $homepage
    ) {}

    public function data(string $audience): array
    {
        // Product type is gone from the catalog, so the navbar's Products
        // dropdown and the mega menu are both fed from `categories`, which
        // already carries a visible-product count per row.
        return [
            'categories' => $this->navigation->categories($audience),
            'categoryMenu' => $this->navigation->categoryMenu($audience, 12, 8),
            'featuredProducts' => $this->navigation->featuredProducts($audience),
            'topbarMessages' => $this->navigation->topbarMessages(),
            'dealProducts' => $this->homepage->dealProducts($audience, self::DEAL_LIMIT),
        ];
    }

    public function emptyData(): array
    {
        return [
            'categories' => collect(),
            'categoryMenu' => collect(),
            'featuredProducts' => collect(),
            'topbarMessages' => collect(),
            'dealProducts' => collect(),
        ];
    }
}
