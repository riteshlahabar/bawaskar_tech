<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use App\Services\Catalog\Api\HomepageCatalogService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class HomepageCatalogServiceTest extends TestCase
{
    public function test_service_builds_existing_homepage_shape_from_replaceable_dependencies(): void
    {
        $section = new ProductHomepageSection([
            'section_key' => 'featured',
            'title' => 'Featured',
            'subtitle' => 'Selected products',
            'section_type' => 'product_section',
            'layout_type' => 'grid',
            'source_type' => 'featured_products',
            'sort_order' => 2,
            'product_limit' => 8,
        ]);
        $section->id = 10;

        $item = new ProductHomepageSectionItem(['is_active' => true, 'sort_order' => 1]);
        $item->id = 11;
        $section->setRelation('items', collect([$item]));

        $category = new Category;
        $category->id = 12;

        $product = new Product;
        $product->id = 13;

        $repository = new class($section, $category, $product) implements HomepageCatalogRepositoryContract
        {
            public function __construct(
                private readonly ProductHomepageSection $section,
                private readonly Category $category,
                private readonly Product $product
            ) {}

            public function activeSections(): Collection
            {
                return collect([$this->section]);
            }

            public function activeCategories(): Collection
            {
                return collect([$this->category]);
            }

            public function productsForSection(
                ProductHomepageSection $section,
                int $limit,
                string $audience
            ): Collection {
                return collect([$this->product]);
            }

            public function fallbackBanners(ProductHomepageSection $section): Collection
            {
                return collect();
            }

            public function legacyBanners(): Collection
            {
                return collect();
            }
        };

        $categoryPresenter = new class implements CategoryCatalogPresenterContract
        {
            public function present(Category $category): array
            {
                return ['id' => $category->id];
            }
        };

        $productPresenter = new class implements ProductCatalogPresenterContract
        {
            public function present(Product $product): array
            {
                return ['id' => $product->id];
            }
        };

        $homepagePresenter = new class implements HomepageCatalogPresenterContract
        {
            public function item(ProductHomepageSectionItem $item): array
            {
                return ['id' => $item->id];
            }

            public function fallbackBanner(StorefrontBanner $banner): array
            {
                return ['id' => $banner->id];
            }

            public function legacyBanner(StorefrontBanner $banner): array
            {
                return ['id' => $banner->id];
            }
        };

        $result = (new HomepageCatalogService(
            $repository,
            $categoryPresenter,
            $productPresenter,
            $homepagePresenter
        ))->homepage('customer');

        $this->assertSame([['id' => 12]], $result['categories']->all());
        $this->assertSame('featured', $result['rows']->first()['section_key']);
        $this->assertSame([['id' => 11]], $result['rows']->first()['items']->all());
        $this->assertSame([['id' => 13]], $result['rows']->first()['products']->all());
        $this->assertSame([], $result['banners']->all());
    }
}
