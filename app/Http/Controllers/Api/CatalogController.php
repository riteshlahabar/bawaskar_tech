<?php

namespace App\Http\Controllers\Api;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Communication\AppTranslation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends ApiController
{
    public function categories(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'en')->toString();
        $cacheKey = 'catalog.categories.'.$this->catalogCacheVersion().'.'.$locale;

        $categories = Cache::remember($cacheKey, now()->addMinutes($this->catalogCacheMinutes()), function () use ($locale) {
            return Category::query()
                ->with(['translations' => fn ($query) => $query->where('locale', $locale), 'children'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });

        return $this->success(['categories' => $categories]);
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

    private function catalogCacheMinutes(): int
    {
        return max(1, (int) env('CATALOG_CACHE_MINUTES', 10));
    }
}