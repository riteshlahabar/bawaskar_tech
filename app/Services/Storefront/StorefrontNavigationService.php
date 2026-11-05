<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Contracts\Storefront\StorefrontNavigationContract;

final class StorefrontNavigationService implements StorefrontNavigationContract
{
    /** The Fastkart "Deal Today" popup is laid out for four products. */
    private const DEAL_LIMIT = 4;

    public function __construct(
        private readonly StorefrontNavigationRepositoryContract $navigation
    ) {}

    public function data(string $audience): array
    {
        $featuredProducts = $this->navigation->featuredProducts($audience);
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $this->navigation->fallbackProducts($audience);
        }

        // Product type is gone from the catalog, so the navbar's Products
        // dropdown and the mega menu are both fed from `categories`, which
        // already carries a visible-product count per row.
        return [
            'categories' => $this->navigation->categories($audience),
            'categoryMenu' => $this->navigation->categoryMenu($audience, 12, 8),
            'featuredProducts' => $featuredProducts,
            'topbarMessages' => $this->navigation->topbarMessages(),
            'dealProducts' => $this->navigation->dealProducts($audience, self::DEAL_LIMIT),
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
