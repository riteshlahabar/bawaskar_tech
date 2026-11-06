<?php

namespace App\Contracts\Storefront\Repositories;

use Illuminate\Support\Collection;

interface StorefrontNavigationRepositoryContract
{
    public function categories(string $audience): Collection;

    public function categoryMenu(string $audience, int $categoryLimit, int $productLimit): Collection;

    /**
     * The navbar's featured strip. Products carry no "Featured" flag any more
     * (that whole tab left the Products form), so this is simply the catalog's
     * own display order.
     */
    public function featuredProducts(string $audience): Collection;

    /** Active header top-bar messages in sort order. */
    public function topbarMessages(): Collection;
}
