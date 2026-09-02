<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Services\Storefront\StorefrontNavigationService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StorefrontNavigationServiceTest extends TestCase
{
    public function test_navigation_keeps_existing_product_type_and_featured_fallback_shape(): void
    {
        $category = new Category;
        $category->id = 1;
        $type = new Product;
        $type->product_type = 'medicine';
        $type->products_count = 4;
        $fallback = new Product;
        $fallback->id = 5;

        $repository = new class($category, $type, $fallback) implements StorefrontNavigationRepositoryContract
        {
            public function __construct(
                private readonly Category $category,
                private readonly Product $type,
                private readonly Product $fallback
            ) {}

            public function categories(string $audience): Collection
            {
                return collect([$this->category]);
            }

            public function productTypeCounts(string $audience): Collection
            {
                return collect([$this->type]);
            }

            public function featuredProducts(string $audience): Collection
            {
                return collect();
            }

            public function fallbackProducts(string $audience): Collection
            {
                return collect([$this->fallback]);
            }
        };

        $result = (new StorefrontNavigationService($repository))->data('customer');

        $this->assertSame('Medicine', $result['productTypes']->first()['name']);
        $this->assertSame(4, $result['productTypes']->first()['products_count']);
        $this->assertSame([5], $result['featuredProducts']->pluck('id')->all());
        $this->assertSame([1], $result['categories']->pluck('id')->all());
    }
}
