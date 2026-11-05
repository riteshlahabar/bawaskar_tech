<?php

namespace App\Presenters\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Support\Str;

final class HomepageCatalogPresenter implements HomepageCatalogPresenterContract
{
    public function __construct(private readonly CatalogTextTranslatorContract $translator) {}

    public function item(ProductHomepageSectionItem $item): array
    {
        return [
            'id' => $item->id,
            // The banner's link. `productEntry()` already sent this key, so the
            // payload shape is unchanged for the apps now that a content row
            // carries the product instead of the other way round.
            'product_id' => $item->product_id,
            'slot' => $item->slot,
            'title' => $this->entry($item->title),
            'subtitle' => $this->entry($item->subtitle),
            'description' => $this->entry($item->description),
            'highlight_text' => $this->entry($item->highlight_text),
            'discount_text' => $this->entry($item->discount_text),
            'validity_text' => $this->entry($item->validity_text),
            'coupon_code' => $item->coupon_code,
            'button_text' => $this->button($item->button_text),
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
            'title' => $this->entry($banner->title),
            'subtitle' => $this->entry($banner->subtitle),
            'description' => $this->entry($banner->description),
            'highlight_text' => null,
            'discount_text' => $this->entry($banner->subtitle),
            'validity_text' => null,
            'coupon_code' => null,
            'button_text' => $this->button($banner->button_text),
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
            'title' => $this->entry($banner->title),
            'subtitle' => $this->entry($banner->subtitle),
            'description' => $this->entry($banner->description),
            'button_text' => $this->button($banner->button_text),
            'button_url' => $banner->button_url,
            'image_url' => $this->assetUrl($banner->image_path),
        ];
    }

    private function entry(?string $text): ?string
    {
        return $this->translator->text($text, 'homepage_entry');
    }

    private function button(?string $text): ?string
    {
        return $this->translator->text($text, 'homepage_button');
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }
}
