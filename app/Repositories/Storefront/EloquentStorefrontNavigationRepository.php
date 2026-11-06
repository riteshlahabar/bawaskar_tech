<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Storefront\StorefrontTopbarMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentStorefrontNavigationRepository implements StorefrontNavigationRepositoryContract
{
    public function categories(string $audience): Collection
    {
        return Category::query()
            ->with(['translations'])
            ->withCount(['products' => fn (Builder $query) => $query->visibleFor($audience)])
            ->where('is_active', true)
            // Alphabetical: Categories no longer carry an admin-editable sort
            // field, and this list also feeds the navbar Products dropdown and
            // the mega menu since item 44.
            ->orderBy('name')
            ->limit(12)
            ->get();
    }

    public function categoryMenu(string $audience, int $categoryLimit, int $productLimit): Collection
    {
        $categories = $this->categories($audience)->take($categoryLimit)->values();

        if ($categories->isEmpty()) {
            return $categories;
        }

        $grouped = Product::query()
            ->visibleFor($audience)
            ->with('translations')
            ->whereIn('category_id', $categories->pluck('id')->all())
            ->orderBy('category_id')
            ->storefrontOrder()
            ->limit($categories->count() * $productLimit * 3)
            ->get(['id', 'name', 'category_id', 'sort_order'])
            ->groupBy('category_id');

        return $categories->each(function (Category $category) use ($grouped, $productLimit): void {
            $category->setAttribute(
                'menu_products',
                collect($grouped->get($category->getKey(), collect()))->take($productLimit)->values()
            );
        });
    }

    public function featuredProducts(string $audience): Collection
    {
        return $this->productQuery($audience)
            ->storefrontOrder()
            ->limit(6)
            ->get();
    }

    public function topbarMessages(): Collection
    {
        return StorefrontTopbarMessage::query()
            ->where('is_active', true)
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
            ->visibleFor($audience);
    }
}
