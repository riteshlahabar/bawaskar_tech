<?php

namespace App\Repositories\Catalog\Api;

use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentHomepageCatalogRepository implements HomepageCatalogRepositoryContract
{
    public function activeSections(): Collection
    {
        return ProductHomepageSection::query()
            ->with([
                'category.translations',
                'items' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                // The product a banner links to, so it is not one query per banner.
                'items.product',
            ])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function activeCategories(): Collection
    {
        return Category::query()
            ->with('translations')
            ->where('is_active', true)
            // Categories carry no homepage placement or sort field any more,
            // so every category list is alphabetical.
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    public function productsForSection(
        ProductHomepageSection $section,
        int $limit,
        string $audience
    ): Collection {
        // Top Selling picks its products one per content row, so row order is
        // the display order and row 1 is the Special Offer card. Same rule as
        // the website, so the apps never show a different Top Selling row.
        if ($section->usesContentRowProducts()) {
            return $this->rowProducts($section, $audience);
        }

        $assigned = $this->productQuery($audience)
            ->where('show_on_homepage', true)
            ->where('homepage_section_id', $section->id)
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        $query = $this->productQuery($audience)->where('show_on_homepage', true);

        if ($section->source_type === 'category_products' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        } elseif ($section->source_type === 'latest_products') {
            $query->latest('id');
        } else {
            return collect();
        }

        return $query
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * The products a row carries on its own content rows, in row order.
     *
     * @return Collection<int, Product>
     */
    private function rowProducts(ProductHomepageSection $section, string $audience): Collection
    {
        $ids = $section->items
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $products = $this->productQuery($audience)
            ->whereIn('id', $ids->all())
            ->get()
            ->keyBy('id');

        return $ids
            ->map(fn (int $id): ?Product => $products->get($id))
            ->filter()
            ->values();
    }

    public function fallbackBanners(ProductHomepageSection $section): Collection
    {
        $placement = match ($section->section_type) {
            'hero_slider' => 'hero_main',
            'top_small_banners' => 'promo_small',
            'coupon_section' => 'bank_offer',
            default => null,
        };

        if (! $placement) {
            return collect();
        }

        return StorefrontBanner::query()
            ->where('is_active', true)
            ->where('placement', $placement)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function legacyBanners(): Collection
    {
        return StorefrontBanner::query()
            ->where('is_active', true)
            ->where('placement', 'hero_main')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    private function productQuery(string $audience): Builder
    {
        return Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images'])
            ->visibleFor($audience);
    }
}
