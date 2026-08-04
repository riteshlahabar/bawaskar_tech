<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontServiceBlock;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

            $products = $data['products'] ?? $this->storefrontProductQuery()
                ->storefrontOrder()
                ->limit(24)
                ->get();

            $homeContent = $this->homeContent();
        } catch (\Throwable) {
            $categories = collect();
            $products = collect();
            $homeContent = $this->emptyHomeContent();
        }

        return view('store.pages.'.$page, array_merge([
            'categories' => $categories,
            'products' => $products,
            'homeContent' => $homeContent,
            'selectedCategory' => null,
            'storeProduct' => null,
            'relatedProducts' => collect(),
        ], $data));
    }

    private function homeContent(): array
    {
        $sections = StorefrontSection::query()
            ->with(['category', 'sectionProducts.product.images', 'sectionProducts.product.category', 'sectionProducts.product.brand', 'sectionProducts.product.unit', 'sectionProducts.product.inventoryBatches'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return [
            'banners' => StorefrontBanner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
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
            ->where('section_type', 'product')
            ->map(function (StorefrontSection $section): array {
                $limit = max(1, min(24, (int) $section->product_limit));
                $products = $this->productsForSection($section, $limit);

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