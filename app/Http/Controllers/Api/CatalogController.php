<?php

namespace App\Http\Controllers\Api;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Communication\AppTranslation;
use App\Models\Storefront\StorefrontBanner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CatalogController extends ApiController
{
    public function categories(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'en')->toString();
        $cacheKey = 'catalog.categories.'.$this->catalogCacheVersion().'.'.$locale;

        $categories = Cache::remember($cacheKey, now()->addMinutes($this->catalogCacheMinutes()), function () use ($locale) {
            return Category::query()
                ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });

        return $this->success(['categories' => $categories]);
    }

    public function homepage(Request $request): JsonResponse
    {
        $audience = $request->string('audience', 'customer')->toString() === 'dealer' ? 'dealer' : 'customer';
        if ($audience === 'dealer') {
            $user = $this->user($request);
            $allowed = $user && in_array($user->role, [User::ROLE_DEALER, User::ROLE_SALESMAN, User::ROLE_ADMIN], true);
            if (! $allowed) {
                return $this->fail('Dealer homepage requires approved dealer login.', $user ? 403 : 401);
            }
        }

        $sections = ProductHomepageSection::query()
            ->with(['category.translations', 'items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $categories = Category::query()
            ->with('translations')
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN show_on_homepage = 1 THEN 0 ELSE 1 END')
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($sections->isEmpty()) {
            return $this->success([
                'banners' => $this->legacyHomepageBanners(),
                'categories' => $categories->map(fn (Category $category): array => $this->serializeCategory($category))->values(),
                'rows' => [],
            ]);
        }

        $rows = $sections->map(function (ProductHomepageSection $section) use ($audience): array {
            $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));

            return [
                'section_key' => $section->section_key,
                'title' => $section->title,
                'subtitle' => $section->subtitle,
                'section_type' => $section->section_type,
                'layout_type' => $section->layout_type,
                'source_type' => $section->source_type,
                'sort_order' => $section->sort_order,
                'items' => $this->homepageSectionItems($section),
                'products' => $this->homepageProducts($section, $limit, $audience)
                    ->map(fn (Product $product): array => $this->serializeProduct($product))
                    ->values(),
            ];
        })->values();

        $heroBanners = $rows
            ->where('section_type', 'hero_slider')
            ->flatMap(fn (array $row) => $row['items'])
            ->values();

        return $this->success([
            'banners' => $heroBanners,
            'categories' => $categories->map(fn (Category $category): array => $this->serializeCategory($category))->values(),
            'rows' => $rows,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $requestedAudience = $request->string('audience', 'customer')->toString();
        $user = $this->user($request);

        if ($requestedAudience === 'dealer') {
            $isAllowedDealerCatalogUser = $user && in_array($user->role, [User::ROLE_DEALER, User::ROLE_SALESMAN, User::ROLE_ADMIN], true);
            if (! $isAllowedDealerCatalogUser) {
                return $this->fail('Dealer catalog requires approved dealer login.', $user ? 403 : 401);
            }
            $audience = 'dealer';
        } elseif ($requestedAudience === 'customer') {
            $audience = 'customer';
        } else {
            $audience = $user?->role === User::ROLE_DEALER || $user?->role === User::ROLE_SALESMAN ? 'dealer' : 'customer';
        }

        $filters = [
            'audience' => $audience,
            'category_id' => $request->integer('category_id') ?: null,
            'search' => trim($request->string('search')->toString()),
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => min(max(1, $request->integer('per_page', 20)), 100),
        ];

        $cacheKey = 'catalog.products.'.$this->catalogCacheVersion().'.'.sha1(json_encode($filters));

        $products = Cache::remember($cacheKey, now()->addMinutes($this->catalogCacheMinutes()), function () use ($filters) {
            return Product::query()
                ->with(['category', 'brand', 'unit', 'images'])
                ->visibleFor($filters['audience'])
                ->when($filters['category_id'], fn ($query) => $query->where('category_id', $filters['category_id']))
                ->when($filters['search'] !== '', fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%'))
                ->latest()
                ->paginate($filters['per_page'], ['*'], 'page', $filters['page']);
        });

        return $this->success(['products' => $products, 'price_type' => $audience === 'dealer' ? 'dealer_price' : 'customer_price']);
    }

    public function translations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $cacheKey = 'catalog.translations.'.$this->catalogCacheVersion().'.'.$validated['locale'];

        $translations = Cache::remember($cacheKey, now()->addMinutes($this->catalogCacheMinutes()), function () use ($validated) {
            return AppTranslation::query()
                ->where('locale', $validated['locale'])
                ->where('is_active', true)
                ->pluck('value', 'translation_key');
        });

        return $this->success(['locale' => $validated['locale'], 'translations' => $translations]);
    }

    private function catalogCacheVersion(): int
    {
        return (int) Cache::get('catalog_cache_version', 1);
    }

    private function homepageProducts(ProductHomepageSection $section, int $limit, string $audience)
    {
        $assigned = $this->homepageProductQuery($audience)
            ->where('show_on_homepage', true)
            ->where('homepage_section_id', $section->id)
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($section->source_type === 'top_selling_products' || $section->section_type === 'top_selling_section') {
            $topSelling = $this->homepageProductQuery($audience)
                ->where('show_on_homepage', true)
                ->where('is_top_selling', true)
                ->orderBy('homepage_sort_order')
                ->orderBy('sort_order')
                ->latest('id')
                ->limit($limit)
                ->get();

            return $assigned->concat($topSelling)->unique('id')->values();
        }

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        $query = $this->homepageProductQuery($audience)->where('show_on_homepage', true);

        if ($section->source_type === 'category_products' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        } elseif ($section->source_type === 'featured_products') {
            $query->where('is_featured', true);
        } elseif ($section->source_type === 'latest_products') {
            $query->latest('id');
        } elseif ($section->source_type === 'offer_products') {
            $query->where('is_offer_product', true);
        } elseif ($section->source_type === 'deal_timer_product') {
            $query->where('is_deal_timer_product', true);
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

    private function homepageProductQuery(string $audience)
    {
        return Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images'])
            ->visibleFor($audience);
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->storefront_name,
            'sku' => $product->sku,
            'customer_price' => $product->customer_price,
            'dealer_price' => $product->dealer_price,
            'mrp' => $product->mrp,
            'gst_percent' => $product->gst_percent,
            'product_type' => $product->product_type,
            'description' => $product->storefront_description,
            'short_description' => $product->short_description,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->storefront_name,
            'unit_name' => $product->unit?->name,
            'image_url' => $product->storefront_image_url,
            'homepage_image_url' => $this->assetUrl($product->homepage_image_path),
            'homepage_mobile_image_url' => $this->assetUrl($product->homepage_mobile_image_path),
            'is_featured' => $product->is_featured,
            'is_trending' => $product->is_trending,
            'is_top_selling' => $product->is_top_selling,
            'is_new_arrival' => $product->is_new_arrival,
            'is_offer_product' => $product->is_offer_product,
        ];
    }

    private function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->storefront_name,
            'slug' => $category->slug,
            'image_url' => $category->storefront_image_url,
        ];
    }

    private function serializeHomepageItem($item): array
    {
        return [
            'id' => $item->id,
            'slot' => $item->slot,
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'description' => $item->description,
            'highlight_text' => $item->highlight_text,
            'discount_text' => $item->discount_text,
            'validity_text' => $item->validity_text,
            'coupon_code' => $item->coupon_code,
            'button_text' => $item->button_text,
            'button_url' => $item->button_url,
            'image_url' => $this->assetUrl($item->image_path),
            'mobile_image_url' => $this->assetUrl($item->mobile_image_path),
            'logo_image_url' => $this->assetUrl($item->logo_image_path),
            'offer_image_url' => $this->assetUrl($item->offer_image_path),
            'background_color' => $item->background_color,
            'text_color' => $item->text_color,
        ];
    }

    private function homepageSectionItems(ProductHomepageSection $section)
    {
        $items = $section->items
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->map(fn ($item): array => $this->serializeHomepageItem($item))
            ->values();

        if ($items->isNotEmpty()) {
            return $items;
        }

        $placement = match ($section->section_type) {
            'hero_slider' => 'hero_main',
            'top_small_banners' => 'promo_small',
            'coupon_section' => 'bank_offer',
            default => null,
        };

        if (! $placement) {
            return $items;
        }

        return StorefrontBanner::query()
            ->where('is_active', true)
            ->where('placement', $placement)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (StorefrontBanner $banner): array => [
                'id' => $banner->id,
                'slot' => $banner->placement,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'description' => $banner->description,
                'highlight_text' => null,
                'discount_text' => $banner->subtitle,
                'validity_text' => null,
                'coupon_code' => null,
                'button_text' => $banner->button_text,
                'button_url' => $banner->button_url,
                'image_url' => $this->assetUrl($banner->image_path),
                'mobile_image_url' => null,
                'logo_image_url' => null,
                'offer_image_url' => null,
                'background_color' => null,
                'text_color' => null,
            ])
            ->values();
    }

    private function legacyHomepageBanners()
    {
        return StorefrontBanner::query()
            ->where('is_active', true)
            ->where('placement', 'hero_main')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (StorefrontBanner $banner): array => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'description' => $banner->description,
                'button_text' => $banner->button_text,
                'button_url' => $banner->button_url,
                'image_url' => $this->assetUrl($banner->image_path),
            ])
            ->values();
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }

    private function catalogCacheMinutes(): int
    {
        return max(1, (int) env('CATALOG_CACHE_MINUTES', 10));
    }
}