<?php

namespace App\Presenters\Catalog\Api;

use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
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
