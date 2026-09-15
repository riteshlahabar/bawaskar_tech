<?php

namespace App\Presenters\Catalog\Api;

use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Support\Str;

final class HomepageCatalogPresenter implements HomepageCatalogPresenterContract
{
    public function item(ProductHomepageSectionItem $item): array
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

    /**
     * Mirrors the storefront template's product entry helpers, so the apps
     * show the same banner / offer content as the website.
     */
    public function productEntry(Product $product): array
    {
        $image = $product->homepage_image_path ?: data_get($product, 'images.0.path');

        return [
            'id' => $product->id,
            'product_id' => $product->id,
            'slot' => 'product',
            'title' => $product->homepage_title ?: $product->storefront_name,
            'subtitle' => $product->homepage_subtitle ?: $product->sale_badge_text,
            'description' => $product->homepage_description ?: $product->short_description,
            'highlight_text' => $product->homepage_highlight_text,
            'discount_text' => $product->homepage_discount_text,
            'validity_text' => $product->homepage_validity_text,
            'coupon_code' => $product->homepage_coupon_code,
            'button_text' => $product->homepage_button_text,
            'button_url' => $product->homepage_button_url,
            'image_url' => $this->assetUrl($image),
            'mobile_image_url' => $this->assetUrl($product->homepage_mobile_image_path),
            'logo_image_url' => $this->assetUrl($product->homepage_logo_image_path ?: $image),
            'offer_image_url' => $this->assetUrl($product->homepage_offer_image_path ?: $image),
            'background_color' => $product->homepage_background_color,
            'text_color' => $product->homepage_text_color,
        ];
    }

    public function fallbackBanner(StorefrontBanner $banner): array
    {
        return [
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
        ];
    }

    public function legacyBanner(StorefrontBanner $banner): array
    {
        return [
            'id' => $banner->id,
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'description' => $banner->description,
            'button_text' => $banner->button_text,
            'button_url' => $banner->button_url,
            'image_url' => $this->assetUrl($banner->image_path),
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }
}
