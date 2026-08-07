<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Communication\Language;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontServiceBlock;
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

        return $this->render($request, 'shop-left-sidebar', [
            'selectedCategory' => $category,
            'products' => $this->storefrontProductQuery()
                ->where('category_id', $category->getKey())
                ->storefrontOrder()
                ->paginate(24)
                ->withQueryString(),
        ]);
    }

    public function product(Request $request, Product $product): View
    {
        abort_unless($product->is_active && $product->is_visible_to_customers, 404);

        $product->load(['category', 'brand', 'unit', 'images', 'inventoryBatches']);

        $relatedProducts = $this->storefrontProductQuery()
            ->when($product->category_id, fn (Builder $query) => $query->where('category_id', $product->category_id))
            ->whereKeyNot($product->getKey())
            ->storefrontOrder()
            ->limit(8)
            ->get();

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
        try {
            $categories = $data['categories'] ?? Category::query()
                ->withCount(['products' => fn (Builder $query) => $query->visibleFor('customer')])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(18)
                ->get();

            $products = $data['products'] ?? ($page === 'shop-left-sidebar'
                ? $this->shopProductQuery($request)->paginate(24)->withQueryString()
                : $this->storefrontProductQuery()->storefrontOrder()->limit(24)->get());

            $homeContent = $this->homeContent();
            $storefrontNavigation = $this->navigationData();
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
    private function shopProductQuery(Request $request): Builder
    {
        $query = $this->storefrontProductQuery();

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

    private function navigationData(): array
    {
        $productTypeLabels = $this->productTypeLabels();

        $categories = Category::query()

            ->withCount(['products' => fn (Builder $query) => $query->visibleFor('customer')])
            ->where('is_active', true)
            
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        $productTypes = Product::query()
            ->visibleFor('customer')
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

        $featuredProducts = $this->storefrontProductQuery()
            ->where('is_featured', true)
            ->storefrontOrder()
            ->limit(6)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $this->storefrontProductQuery()->storefrontOrder()->limit(6)->get();
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

    private function homeContent(): array
    {
        $homepageSections = ProductHomepageSection::query()
            ->with(['category', 'items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($homepageSections->isNotEmpty()) {
            return [
                'homepageRows' => $this->resolveHomepageRows($homepageSections),
                'homepageSettings' => $homepageSections,
                'banners' => $this->homepageSettingBanners($homepageSections),
                'sections' => $homepageSections->keyBy('section_key'),
                'productSections' => $this->resolveHomeProductSections($homepageSections),
                'services' => $this->homepageSettingItems($homepageSections, 'service_section'),
                'topSellingProducts' => $this->topSellingProducts(),
                'dealTimerProduct' => $this->dealTimerProduct(),
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
            'productSections' => $this->resolveHomeProductSections($sections),
            'services' => StorefrontServiceBlock::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'footerLinks' => StorefrontFooterLink::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('link_group'),
        ];
    }

    private function resolveHomeProductSections(Collection $sections): Collection
    {
        return $sections
            ->filter(fn ($section): bool => in_array((string) $section->section_type, ['product', 'product_section'], true))
            ->map(function ($section): array {
                $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));
                $products = $section instanceof ProductHomepageSection
                    ? $this->productsForHomepageSection($section, $limit)
                    : $this->productsForSection($section, $limit);

                return ['section' => $section, 'products' => $products];
            })
            ->filter(fn (array $entry): bool => $entry['products']->isNotEmpty())
            ->values();
    }

    private function productsForSection(StorefrontSection $section, int $limit): Collection
    {
        if ($section->source_type === 'manual') {
            return $section->sectionProducts
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->pluck('product')
                ->filter(fn (?Product $product): bool => $product && $product->is_active && $product->is_visible_to_customers)
                ->take($limit)
                ->values();
        }

        $query = $this->storefrontProductQuery();

        if ($section->source_type === 'category' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        }

        if ($section->source_type === 'featured') {
            $query->where('is_featured', true);
        }

        return $query->storefrontOrder()->limit($limit)->get();
    }

    private function resolveHomepageRows(Collection $sections): Collection
    {
        return $sections->map(function (ProductHomepageSection $section): array {
            $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));

            return [
                'section' => $section,
                'items' => $section->items->where('is_active', true)->sortBy('sort_order')->values(),
                'products' => in_array($section->section_type, ['product_section', 'top_selling_section'], true)
                    ? $this->productsForHomepageSection($section, $limit)
                    : collect(),
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

    private function productsForHomepageSection(ProductHomepageSection $section, int $limit): Collection
    {
        $query = $this->storefrontProductQuery()
            ->where('show_on_homepage', true);

        match ($section->source_type) {
            'category_products' => $section->category_id ? $query->where('category_id', $section->category_id) : null,
            'featured_products' => $query->where('is_featured', true),
            'offer_products' => $query->where('is_offer_product', true),
            'top_selling_products' => $query->where('is_top_selling', true),
            'latest_products' => $query->latest('id'),
            default => null,
        };

        if ($section->section_type === 'top_selling_section' && ! in_array($section->source_type, ['category_products', 'featured_products', 'offer_products', 'latest_products'], true)) {
            $query->where('is_top_selling', true);
        }

        return $query->storefrontOrder()->limit($limit)->get();
    }

    private function topSellingProducts(): Collection
    {
        return $this->storefrontProductQuery()
            ->where('show_on_homepage', true)
            ->where('is_top_selling', true)
            ->storefrontOrder()
            ->limit(8)
            ->get();
    }

    private function dealTimerProduct(): ?Product
    {
        return $this->storefrontProductQuery()
            ->where('show_on_homepage', true)
            ->where('is_deal_timer_product', true)
            ->storefrontOrder()
            ->first();
    }

    private function storefrontProductQuery(): Builder
    {
        return Product::query()
            ->with(['category', 'brand', 'unit', 'images', 'inventoryBatches'])
            ->visibleFor('customer');
    }

    private function emptyHomeContent(): array
    {
        return [
            'banners' => collect(),
            'sections' => collect(),
            'productSections' => collect(),
            'services' => collect(),
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
}