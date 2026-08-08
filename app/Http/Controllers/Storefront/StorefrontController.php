<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Communication\Language;
use App\Models\Sales\Order as StoreOrder;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontServiceBlock;
use App\Models\User;
use App\Services\StorefrontSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function home(Request $request): View
    {
        return $this->render($request, 'index-5');
    }

    public function page(Request $request, string $page): View
    {
        abort_unless(in_array($page, config('storefront.pages', []), true), 404);

        return $this->render($request, $page);
    }

    public function category(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);
        $audience = $this->storefrontAudience($request);

        return $this->render($request, 'shop-left-sidebar', [
            'selectedCategory' => $category,
            'products' => $this->storefrontProductQuery($audience)
                ->where('category_id', $category->getKey())
                ->storefrontOrder()
                ->paginate(24)
                ->withQueryString(),
        ]);
    }

    public function product(Request $request, Product $product): View
    {
        $audience = $this->storefrontAudience($request);
        $visibleColumn = $audience === 'dealer' ? 'is_visible_to_dealers' : 'is_visible_to_customers';

        abort_unless($product->is_active && (bool) $product->{$visibleColumn}, 404);

        $product->load([
            'category',
            'brand',
            'unit',
            'images',
            'inventoryBatches',
            'variants' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id'),
            'relatedProductLinks.relatedProduct.category',
            'relatedProductLinks.relatedProduct.brand',
            'relatedProductLinks.relatedProduct.unit',
            'relatedProductLinks.relatedProduct.images',
            'relatedProductLinks.relatedProduct.inventoryBatches',
        ]);

        $relatedProducts = $product->relatedProductLinks
            ->sortBy('sort_order')
            ->pluck('relatedProduct')
            ->filter(function (?Product $relatedProduct) use ($visibleColumn): bool {
                return $relatedProduct && $relatedProduct->is_active && (bool) $relatedProduct->{$visibleColumn};
            })
            ->values();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = $this->storefrontProductQuery($audience)
                ->when($product->category_id, fn (Builder $query) => $query->where('category_id', $product->category_id))
                ->whereKeyNot($product->getKey())
                ->storefrontOrder()
                ->limit(8)
                ->get();
        }

        return $this->render($request, 'product-left-thumbnail', [
            'storeProduct' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function switchLanguage(Request $request, string $locale)
    {
        $language = Language::query()->active()->where('code', $locale)->firstOrFail();
        $request->session()->put('store_locale', $language->code);

        return back();
    }

    public function invoicePreview(string $template): View
    {
        abort_unless(in_array($template, config('storefront.invoice_templates', []), true), 404);

        return view('invoices.fastkart-preview.'.$template);
    }

    public function emailPreview(string $template): View
    {
        abort_unless(in_array($template, config('storefront.email_templates', []), true), 404);

        return view('emails.fastkart.'.$template, $this->previewData());
    }

    private function render(Request $request, string $page, array $data = []): View
    {
        $storefrontSession = app(StorefrontSessionService::class);
        $storeUser = $storefrontSession->user($request);
        $audience = $storefrontSession->audience($request);
        $storeCart = $storefrontSession->cartSummary($request);
        $storeOrders = $storeUser ? $this->storeOrders($storeUser) : collect();
        $storePrimaryAddress = $storeUser?->addresses->firstWhere('is_default', true) ?: $storeUser?->addresses->first();
        $storeLastOrder = $this->lastStoreOrder($request, $storeUser);

        try {
            $categories = $data['categories'] ?? Category::query()
                ->withCount(['products' => fn (Builder $query) => $query->visibleFor($audience)])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(18)
                ->get();

            $products = $data['products'] ?? ($page === 'shop-left-sidebar'
                ? $this->shopProductQuery($request, $audience)->paginate(24)->withQueryString()
                : $this->storefrontProductQuery($audience)->storefrontOrder()->limit(24)->get());

            $homeContent = $this->homeContent($audience);
            $storefrontNavigation = $this->navigationData($audience);
            [$storeLanguages, $currentStoreLanguage] = $this->languageData($request);
        } catch (\Throwable) {
            $categories = collect();
            $products = collect();
            $homeContent = $this->emptyHomeContent();
            $storefrontNavigation = $this->emptyNavigationData();
            [$storeLanguages, $currentStoreLanguage] = $this->emptyLanguageData();
        }

        return view('store.pages.'.$page, array_merge([
            'categories' => $categories,
            'products' => $products,
            'homeContent' => $homeContent,
            'storefrontNavigation' => $storefrontNavigation,
            'storeLanguages' => $storeLanguages,
            'currentStoreLanguage' => $currentStoreLanguage,
            'selectedCategory' => null,
            'selectedProductType' => $request->query('product_type'),
            'searchQuery' => $request->query('search'),
            'storeProduct' => null,
            'relatedProducts' => collect(),
            'storeUser' => $storeUser,
            'storeAudience' => $audience,
            'storeCart' => $storeCart,
            'storeCartCount' => $storeCart['count'],
            'storeOrders' => $storeOrders,
            'storePrimaryAddress' => $storePrimaryAddress,
            'storeLastOrder' => $storeLastOrder,
        ], $data));
    }

    private function languageData(Request $request): array
    {
        $languages = Language::query()->active()->ordered()->get();

        if ($languages->isEmpty()) {
            return $this->emptyLanguageData();
        }

        $requestedLocale = (string) $request->session()->get('store_locale', '');
        $currentLanguage = $languages->firstWhere('code', $requestedLocale)
            ?: $languages->firstWhere('is_default', true)
            ?: $languages->firstWhere('code', 'en')
            ?: $languages->first();

        app()->setLocale($currentLanguage->code);

        return [$languages, $currentLanguage];
    }

    private function emptyLanguageData(): array
    {
        $language = new Language([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [collect([$language]), $language];
    }

    private function shopProductQuery(Request $request, string $audience): Builder
    {
        $query = $this->storefrontProductQuery($audience);

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->query('product_type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->storefrontOrder();
    }

    private function navigationData(string $audience): array
    {
        $productTypeLabels = $this->productTypeLabels();

        $categories = Category::query()
            ->withCount(['products' => fn (Builder $query) => $query->visibleFor($audience)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        $productTypes = Product::query()
            ->visibleFor($audience)
            ->whereNotNull('product_type')
            ->where('product_type', '<>', '')
            ->select('product_type', DB::raw('count(*) as products_count'))
            ->groupBy('product_type')
            ->orderBy('product_type')
            ->get()
            ->map(fn (Product $product): array => [
                'slug' => $product->product_type,
                'name' => $productTypeLabels[$product->product_type] ?? $this->formatProductType($product->product_type),
                'products_count' => (int) $product->products_count,
            ]);

        if ($productTypes->isEmpty()) {
            $productTypes = collect($productTypeLabels)->map(fn (string $name, string $slug): array => [
                'slug' => $slug,
                'name' => $name,
                'products_count' => 0,
            ])->values();
        }

        $featuredProducts = $this->storefrontProductQuery($audience)
            ->where('is_featured', true)
            ->storefrontOrder()
            ->limit(6)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $this->storefrontProductQuery($audience)->storefrontOrder()->limit(6)->get();
        }

        return [
            'categories' => $categories,
            'productTypes' => $productTypes,
            'productTypeLabels' => $productTypeLabels,
            'featuredProducts' => $featuredProducts,
        ];
    }

    private function emptyNavigationData(): array
    {
        return [
            'categories' => collect(),
            'productTypes' => collect($this->productTypeLabels())->map(fn (string $name, string $slug): array => [
                'slug' => $slug,
                'name' => $name,
                'products_count' => 0,
            ])->values(),
            'productTypeLabels' => $this->productTypeLabels(),
            'featuredProducts' => collect(),
        ];
    }

    private function productTypeLabels(): array
    {
        return [
            'medicine' => 'Medicine',
            'fertilizer' => 'Fertilizer',
            'seed' => 'Seeds',
            'seeds' => 'Seeds',
            'veterinary' => 'Veterinary Products',
            'veterinary_products' => 'Veterinary Products',
            'equipment' => 'Equipment',
            'other' => 'Other',
        ];
    }

    private function formatProductType(string $productType): string
    {
        return str($productType)->replace(['_', '-'], ' ')->headline()->toString();
    }

    private function homeContent(string $audience): array
    {
        $homepageSections = ProductHomepageSection::query()
            ->with(['category', 'items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($homepageSections->isNotEmpty()) {
            return [
                'homepageRows' => $this->resolveHomepageRows($homepageSections, $audience),
                'homepageSettings' => $homepageSections,
                'banners' => $this->homepageSettingBanners($homepageSections),
                'sections' => $homepageSections->keyBy('section_key'),
                'productSections' => $this->resolveHomeProductSections($homepageSections, $audience),
                'services' => $this->homepageSettingItems($homepageSections, 'service_section'),
                'topSellingProducts' => $this->topSellingProducts($audience),
                'dealTimerProduct' => $this->dealTimerProduct($audience),
                'footerLinks' => StorefrontFooterLink::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('link_group'),
            ];
        }

        $sections = StorefrontSection::query()
            ->with(['category', 'sectionProducts.product.images', 'sectionProducts.product.category', 'sectionProducts.product.brand', 'sectionProducts.product.unit'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return [
            'homepageRows' => collect(),
            'homepageSettings' => collect(),
            'banners' => StorefrontBanner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->groupBy('placement'),
            'sections' => $sections->keyBy('section_key'),
            'productSections' => $this->resolveHomeProductSections($sections, $audience),
            'services' => StorefrontServiceBlock::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'topSellingProducts' => $this->topSellingProducts($audience),
            'dealTimerProduct' => $this->dealTimerProduct($audience),
            'footerLinks' => StorefrontFooterLink::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('link_group'),
        ];
    }

    private function resolveHomeProductSections(Collection $sections, string $audience): Collection
    {
        return $sections
            ->filter(fn ($section): bool => in_array((string) $section->section_type, ['product', 'product_section', 'top_selling_section'], true) || (string) ($section->source_type ?? '') === 'top_selling_products')
            ->map(function ($section) use ($audience): array {
                $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));
                $products = $section instanceof ProductHomepageSection
                    ? $this->productsForHomepageSection($section, $limit, $audience)
                    : $this->productsForSection($section, $limit, $audience);

                return ['section' => $section, 'products' => $products];
            })
            ->filter(fn (array $entry): bool => $entry['products']->isNotEmpty())
            ->values();
    }

    private function productsForSection(StorefrontSection $section, int $limit, string $audience): Collection
    {
        if ($section->source_type === 'manual') {
            $visibleColumn = $audience === 'dealer' ? 'is_visible_to_dealers' : 'is_visible_to_customers';

            return $section->sectionProducts
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->pluck('product')
                ->filter(fn (?Product $product): bool => $product && $product->is_active && (bool) $product->{$visibleColumn})
                ->take($limit)
                ->values();
        }

        $query = $this->storefrontProductQuery($audience);

        if ($section->source_type === 'category' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        }

        if ($section->source_type === 'featured') {
            $query->where('is_featured', true);
        }

        return $query->storefrontOrder()->limit($limit)->get();
    }

    private function resolveHomepageRows(Collection $sections, string $audience): Collection
    {
        return $sections->map(function (ProductHomepageSection $section) use ($audience): array {
            $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));

            return [
                'section' => $section,
                'items' => $section->items->where('is_active', true)->sortBy('sort_order')->values(),
                'products' => $this->productsForHomepageSection($section, $limit, $audience),
            ];
        })->values();
    }

    private function homepageSettingItems(Collection $sections, string $sectionType): Collection
    {
        return $sections
            ->where('section_type', $sectionType)
            ->flatMap(fn (ProductHomepageSection $section) => $section->items->where('is_active', true))
            ->sortBy('sort_order')
            ->values();
    }

    private function homepageSettingBanners(Collection $sections): Collection
    {
        $groups = collect();

        foreach ($sections as $section) {
            $placement = match ($section->section_type) {
                'hero_slider' => 'hero_main',
                'top_small_banners' => 'promo_small',
                'coupon_section' => 'bank_offer',
                'strip_offer_banner' => 'strip_banner',
                'blog_section' => 'blog',
                'offer_section' => $section->layout_type === 'big_small_banner' ? 'footer_promo' : 'middle_promo',
                default => $section->section_key,
            };

            $items = $section->items->where('is_active', true)->sortBy('sort_order')->values();
            if ($items->isEmpty()) {
                continue;
            }

            $groups->put($placement, ($groups->get($placement, collect()))->merge($items));
        }

        return $groups;
    }

    private function productsForHomepageSection(ProductHomepageSection $section, int $limit, string $audience): Collection
    {
        $limit = max(1, $limit ?: (int) ($section->product_limit ?: 8));

        $assigned = $this->storefrontProductQuery($audience)
            ->where('show_on_homepage', true)
            ->where('homepage_section_id', $section->id)
            ->orderBy('homepage_sort_order')
            ->storefrontOrder()
            ->limit($limit)
            ->get();

        if ($section->source_type === 'top_selling_products' || $section->section_type === 'top_selling_section') {
            $topSellingProducts = $this->storefrontProductQuery($audience)
                ->where('show_on_homepage', true)
                ->where('is_top_selling', true)
                ->orderBy('homepage_sort_order')
                ->storefrontOrder()
                ->limit($limit)
                ->get();

            return $assigned
                ->concat($topSellingProducts)
                ->unique('id')
                ->values();
        }

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        $query = $this->storefrontProductQuery($audience)
            ->where('show_on_homepage', true);

        if ($section->source_type === 'category_products' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        } elseif ($section->source_type === 'featured_products') {
            $query->where('is_featured', true);
        } elseif ($section->source_type === 'latest_products') {
            $query->latest('id');
        } elseif ($section->source_type === 'offer_products') {
            $query->where('is_offer_product', true);
        } elseif ($section->source_type === 'top_selling_products' || $section->section_type === 'top_selling_section') {
            $query->where('is_top_selling', true);
        } elseif ($section->source_type === 'deal_timer_product') {
            $query->where('is_deal_timer_product', true);
        } else {
            return collect();
        }

        return $query
            ->orderBy('homepage_sort_order')
            ->storefrontOrder()
            ->limit($limit)
            ->get();
    }

    private function topSellingProducts(string $audience): Collection
    {
        return $this->storefrontProductQuery($audience)
            ->where('show_on_homepage', true)
            ->where('is_top_selling', true)
            ->storefrontOrder()
            ->limit(8)
            ->get();
    }

    private function dealTimerProduct(string $audience): ?Product
    {
        return $this->storefrontProductQuery($audience)
            ->where('show_on_homepage', true)
            ->where('is_deal_timer_product', true)
            ->storefrontOrder()
            ->first();
    }

    private function storefrontProductQuery(string $audience = 'customer'): Builder
    {
        return Product::query()
            ->with(['category', 'brand', 'unit', 'images', 'inventoryBatches'])
            ->visibleFor($audience);
    }

    private function emptyHomeContent(): array
    {
        return [
            'homepageRows' => collect(),
            'homepageSettings' => collect(),
            'banners' => collect(),
            'sections' => collect(),
            'productSections' => collect(),
            'services' => collect(),
            'topSellingProducts' => collect(),
            'dealTimerProduct' => null,
            'footerLinks' => collect(),
        ];
    }

    private function previewData(): array
    {
        return [
            'recipientName' => 'Customer',
            'orderNumber' => 'CO-DEMO-1001',
            'resetUrl' => route('store.page', ['page' => 'forgot']),
            'offerTitle' => 'Special Farmer Medicine Offer',
        ];
    }

    private function storefrontAudience(Request $request): string
    {
        return app(StorefrontSessionService::class)->audience($request);
    }

    private function storeOrders(User $user): Collection
    {
        $query = StoreOrder::query()
            ->with(['items.product.images', 'invoice', 'dispatches', 'salesman']);

        if ($user->role === User::ROLE_DEALER) {
            $query->where('dealer_id', $user->id);
        } else {
            $query->where('customer_id', $user->id);
        }

        return $query->latest()->limit(10)->get();
    }

    private function lastStoreOrder(Request $request, ?User $user): ?StoreOrder
    {
        if (! $user) {
            return null;
        }

        $orderId = app(StorefrontSessionService::class)->lastOrderId($request);
        if (! $orderId) {
            return null;
        }

        $query = StoreOrder::query()
            ->with(['items.product.images', 'invoice', 'dispatches', 'salesman']);

        if ($user->role === User::ROLE_DEALER) {
            $query->where('dealer_id', $user->id);
        } else {
            $query->where('customer_id', $user->id);
        }

        return $query->find($orderId);
    }
}
