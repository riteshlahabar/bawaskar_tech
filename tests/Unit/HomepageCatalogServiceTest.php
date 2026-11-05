<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
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
        $result = $this->homepageFor('product_section');

        $this->assertSame([['id' => 12]], $result['categories']->all());
        $this->assertSame('featured', $result['rows']->first()['section_key']);
        $this->assertSame('homepage_section:Featured', $result['rows']->first()['title'], 'Section titles go through the website translation.');
        $this->assertSame([['id' => 11]], $result['rows']->first()['items']->all());
        $this->assertSame([['id' => 13]], $result['rows']->first()['products']->all());
        $this->assertSame([], $result['banners']->all());
    }

    /**
     * The rule item 47 introduced: a banner, offer, coupon, strip, service,
     * blog or video row is built from its content rows only. Before, a product
     * assigned to such a row won and silently hid the uploaded banner.
     */
    public function test_content_sections_ignore_assigned_products(): void
    {
        foreach (ProductHomepageSection::CONTENT_SECTION_TYPES as $type) {
            $row = $this->homepageFor($type)['rows']->first();

            $this->assertSame([['id' => 11]], $row['items']->all(), "{$type} must show its content row, not the assigned product.");
            $this->assertSame([], $row['products']->all(), "{$type} must not send product cards.");
        }
    }

    public function test_product_sections_still_send_their_products(): void
    {
        foreach (ProductHomepageSection::PRODUCT_SECTION_TYPES as $type) {
            $row = $this->homepageFor($type)['rows']->first();

            $this->assertSame([['id' => 13]], $row['products']->all(), "{$type} must still send its products.");
        }
    }

    public function test_a_content_section_with_no_rows_falls_back_to_storefront_banners(): void
    {
        $row = $this->homepageFor('coupon_section', withProduct: false, withItem: false, withFallback: true)['rows']->first();

        // Live proof this branch matters: the coupon row has no content rows and
        // no products, and renders from `storefront_banners`.
        $this->assertSame([['id' => 99]], $row['items']->all());
    }

    private function homepageFor(
        string $sectionType,
        bool $withProduct = true,
        bool $withItem = true,
        bool $withFallback = false
    ): array {
        $section = new ProductHomepageSection([
            'section_key' => 'featured',
            'title' => 'Featured',
            'subtitle' => 'Selected products',
            'section_type' => $sectionType,
            'layout_type' => 'grid',
            'source_type' => 'featured_products',
            'sort_order' => 2,
            'product_limit' => 8,
        ]);
        $section->id = 10;

        $item = new ProductHomepageSectionItem(['is_active' => true, 'sort_order' => 1]);
        $item->id = 11;
        $section->setRelation('items', $withItem ? collect([$item]) : collect());

        $category = new Category;
        $category->id = 12;

        $product = new Product;
        $product->id = 13;

        $banner = new StorefrontBanner;
        $banner->id = 99;

        $repository = new class($section, $category, $withProduct ? collect([$product]) : collect(), $withFallback ? collect([$banner]) : collect()) implements HomepageCatalogRepositoryContract
        {
            public function __construct(
                private readonly ProductHomepageSection $section,
                private readonly Category $category,
                private readonly Collection $products,
                private readonly Collection $banners
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
                return $this->products;
            }

            public function fallbackBanners(ProductHomepageSection $section): Collection
            {
                return $this->banners;
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

        return (new HomepageCatalogService(
            $repository,
            $categoryPresenter,
            $productPresenter,
            $homepagePresenter,
            new class implements CatalogTextTranslatorContract
            {
                public function text(?string $text, string $group): ?string
                {
                    return $text === null ? null : $group.':'.$text;
                }
            }
        ))->homepage('customer');
    }
}
