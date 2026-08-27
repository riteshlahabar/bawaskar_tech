<?php

namespace App\Contracts\Storefront\Repositories;

use Illuminate\Support\Collection;

interface StorefrontNavigationRepositoryContract
{
    public function categories(string $audience): Collection;

    public function productTypeCounts(string $audience): Collection;

    public function featuredProducts(string $audience): Collection;

    public function fallbackProducts(string $audience): Collection;
}
