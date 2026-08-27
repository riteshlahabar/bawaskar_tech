<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\HomepageCatalogContract;
use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Support\Collection;

final class HomepageCatalogService implements HomepageCatalogContract
{
    public function __construct(
        private readonly HomepageCatalogRepositoryContract $homepage,
        private readonly CategoryCatalogPresenterContract $categories,
        private readonly ProductCatalogPresenterContract $products,
        private readonly HomepageCatalogPresenterContract $presenter
    ) {
    }

    public function homepage(string $audience): array
    {
        $sections = $this->homepage->activeSections();
        $categories = $this->homepage->activeCategories()
            ->map(fn (Category $category): array => $this->categories->present($category))
            ->values();

        if ($sections->isEmpty()) {
            return [
                'banners' => $this->homepage->legacyBanners()
                    ->map(fn (StorefrontBanner $banner): array => $this->presenter->legacyBanner($banner))
                    ->values(),
                'categories' => $categories,
                'rows' => [],
            ];
        }

        $rows = $sections
            ->map(function (ProductHomepageSection $section) use ($audience): array {
                $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));

                return [
                    'section_key' => $section->section_key,
                    'title' => $section->title,
                    'subtitle' => $section->subtitle,
                    'section_type' => $section->section_type,
                    'layout_type' => $section->layout_type,
                    'source_type' => $section->source_type,
                    'sort_order' => $section->sort_order,
                    'items' => $this->sectionItems($section),
                    'products' => $this->homepage
                        ->productsForSection($section, $limit, $audience)
                        ->map(fn (Product $product): array => $this->products->present($product))
                        ->values(),
                ];
            })
            ->values();

        $heroBanners = $rows
            ->where('section_type', 'hero_slider')
            ->flatMap(fn (array $row) => $row['items'])
            ->values();

        return [
            'banners' => $heroBanners,
            'categories' => $categories,
            'rows' => $rows,
        ];
    }

    private function sectionItems(ProductHomepageSection $section): Collection
    {
        $items = $section->items
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->map(fn (ProductHomepageSectionItem $item): array => $this->presenter->item($item))
            ->values();

        if ($items->isNotEmpty()) {
            return $items;
        }

        return $this->homepage->fallbackBanners($section)
            ->map(fn (StorefrontBanner $banner): array => $this->presenter->fallbackBanner($banner))
            ->values();
    }
}
