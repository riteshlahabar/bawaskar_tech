<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontTopbarMessage;
use App\Services\Storefront\StorefrontNavigationService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StorefrontNavigationServiceTest extends TestCase
{
    public function test_navigation_keeps_its_category_and_deal_popup_shape(): void
    {
        $category = new Category;
        $category->id = 1;
        $featured = new Product;
        $featured->id = 5;
        $deal = new Product;
        $deal->id = 7;

        $navigation = new class($category, $featured) implements StorefrontNavigationRepositoryContract
        {
            public function __construct(
                private readonly Category $category,
                private readonly Product $featured
            ) {}

            public function categories(string $audience): Collection
            {
                return collect([$this->category]);
            }

            public function categoryMenu(string $audience, int $categoryLimit, int $productLimit): Collection
            {
                return collect([$this->category]);
            }

            public function featuredProducts(string $audience): Collection
            {
                return collect([$this->featured]);
            }

            public function topbarMessages(): Collection
            {
                return collect([new StorefrontTopbarMessage(['message' => 'Now on sale'])]);
            }
        };

        // The "Deal Today" popup is fed by the homepage repository now: a deal
        // is the first content row of a Top Selling section, not a product flag.
        $homepage = new class($deal) implements StorefrontHomepageRepositoryContract
        {
            public function __construct(private readonly Product $deal) {}

            public function dealProducts(string $audience, int $limit): Collection
            {
                return collect([$this->deal])
                    ->map(fn (Product $product) => $product
                        ->setAttribute('deal_limit', $limit)
                        ->setAttribute('deal_audience', $audience));
            }

            public function homepageSections(): Collection
            {
                return collect();
            }

            public function legacySections(): Collection
            {
                return collect();
            }

            public function legacyBanners(): Collection
            {
                return collect();
            }

            public function serviceBlocks(): Collection
            {
                return collect();
            }

            public function footerLinks(): Collection
            {
                return collect();
            }

            public function productsForLegacySection(StorefrontSection $section, int $limit, string $audience): Collection
            {
                return collect();
            }

            public function productsForHomepageSection(ProductHomepageSection $section, int $limit, string $audience): Collection
            {
                return collect();
            }

            public function topSellingProducts(string $audience): Collection
            {
                return collect();
            }

            public function dealTimerProduct(string $audience): ?Product
            {
                return $this->deal;
            }
        };

        $result = (new StorefrontNavigationService($navigation, $homepage))->data('customer');

        // Product type is gone from the catalog, so the navbar menus read
        // `categories` and nothing publishes a productTypes key any more.
        $this->assertArrayNotHasKey('productTypes', $result);
        $this->assertArrayNotHasKey('productTypeLabels', $result);
        $this->assertSame([1], $result['categories']->pluck('id')->all());
        $this->assertSame([1], $result['categoryMenu']->pluck('id')->all());
        $this->assertSame([5], $result['featuredProducts']->pluck('id')->all());
        $this->assertSame(['Now on sale'], $result['topbarMessages']->pluck('message')->all());
        $this->assertSame([7], $result['dealProducts']->pluck('id')->all());
        $this->assertSame(4, $result['dealProducts']->first()->deal_limit);
        $this->assertSame('customer', $result['dealProducts']->first()->deal_audience);

        $empty = (new StorefrontNavigationService($navigation, $homepage))->emptyData();
        $this->assertArrayNotHasKey('productTypes', $empty);
        $this->assertTrue($empty['topbarMessages']->isEmpty());
        $this->assertTrue($empty['dealProducts']->isEmpty());
    }
}
