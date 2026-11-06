<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontServiceBlock;
use App\Services\Sales\Orders\OrderStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentStorefrontHomepageRepository implements StorefrontHomepageRepositoryContract
{
    public function homepageSections(): Collection
    {
        return ProductHomepageSection::query()
            ->with([
                'category',
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

    public function legacySections(): Collection
    {
        return StorefrontSection::query()
            ->with([
                'category',
                'sectionProducts.product.images',
                'sectionProducts.product.category',
                'sectionProducts.product.brand',
                'sectionProducts.product.unit',
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function legacyBanners(): Collection
    {
        return StorefrontBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->groupBy('placement');
    }

    public function serviceBlocks(): Collection
    {
        return StorefrontServiceBlock::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function footerLinks(): Collection
    {
        return StorefrontFooterLink::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('link_group');
    }

    public function productsForLegacySection(
        StorefrontSection $section,
        int $limit,
        string $audience
    ): Collection {
        if ($section->source_type === 'manual') {
            $visibleColumn = $audience === 'dealer'
                ? 'is_visible_to_dealers'
                : 'is_visible_to_customers';

            return $section->sectionProducts
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->pluck('product')
                ->filter(
                    fn (?Product $product): bool => $product
                        && $product->is_active
                        && (bool) $product->{$visibleColumn}
                )
                ->take($limit)
                ->values();
        }

        $query = $this->productQuery($audience);

        if ($section->source_type === 'category' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        }

        return $query->storefrontOrder()->limit($limit)->get();
    }

    public function productsForHomepageSection(
        ProductHomepageSection $section,
        int $limit,
        string $audience
    ): Collection {
        $limit = max(1, $limit ?: (int) ($section->product_limit ?: 8));

        // Top Selling picks its products one per content row, so row order is
        // the display order and row 1 is the Special Offer card.
        if ($section->usesContentRowProducts()) {
            return $this->rowProducts($section, $audience);
        }

        $assigned = $this->productQuery($audience)
            ->where('show_on_homepage', true)
            ->where('homepage_section_id', $section->id)
            ->orderBy('homepage_sort_order')
            ->storefrontOrder()
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
            ->storefrontOrder()
            ->limit($limit)
            ->get();
    }

    public function topSellingProducts(string $audience): Collection
    {
        return $this->rowProductSections()
            ->flatMap(fn (ProductHomepageSection $section): Collection => $this->rowProducts($section, $audience))
            ->unique('id')
            ->take(8)
            ->values();
    }

    public function dealTimerProduct(string $audience): ?Product
    {
        foreach ($this->rowProductSections() as $section) {
            $product = $this->rowProducts($section, $audience)->first();

            if ($product) {
                return $product;
            }
        }

        return null;
    }

    public function dealProducts(string $audience, int $limit): Collection
    {
        return $this->rowProductSections()
            ->map(fn (ProductHomepageSection $section): ?Product => $this->rowProducts($section, $audience)->first())
            ->filter()
            ->unique('id')
            ->take(max(1, $limit))
            ->values();
    }

    /**
     * The products a row carries on its own content rows, in row order and
     * loaded with everything a product card needs.
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

        // Ordered by the rows, not by the query, and a product that has since
        // been hidden from this audience simply drops out.
        return $ids
            ->map(fn (int $id): ?Product => $products->get($id))
            ->filter()
            ->values();
    }

    /** @return Collection<int, ProductHomepageSection> */
    private function rowProductSections(): Collection
    {
        return ProductHomepageSection::query()
            ->with(['items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->whereIn('section_type', ProductHomepageSection::ROW_PRODUCT_SECTION_TYPES)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function productQuery(string $audience): Builder
    {
        return Product::query()
            ->with([
                'category.translations',
                'brand',
                'unit',
                'images',
                'translations',
                'inventoryBatches',
                'variants.inventoryBatches',
            ])
            // Feeds the deal card's "Sold" counter and its progress bar. It
            // sits here, not on dealTimerProduct(), because the homepage picks
            // the deal product out of this list as often as it takes the
            // dedicated one. Cancelled orders are excluded; everything still in
            // flight counts as sold.
            ->withSum(
                ['orderItems as sold_quantity' => fn (Builder $query): Builder => $query
                    ->whereHas('order', fn (Builder $order): Builder => $order
                        ->where('status', '!=', OrderStatusService::CANCELLED)), ],
                'quantity'
            )
            ->visibleFor($audience);
    }
}
