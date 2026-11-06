<?php

namespace App\Services\Catalog\Homepage;

use App\Models\Catalog\ProductHomepageSection;

/**
 * The single authority on which inputs one content row has, per section type.
 *
 * Mirrors how App\Support\Admin\Forms\ChannelFields is the one authority on
 * Sales channel keys: the editor view, its validation rules and the save all
 * read this class, so a field is declared exactly once.
 *
 * Every set below was confirmed field by field with the user on 2026-11-05
 * against what the live homepage actually holds. Deliberately absent:
 * - no Button URL anywhere. A banner links only through its product, so the
 *   button renders only when a product is picked.
 * - no title/subtitle over hero, small or offer banners. That wording lives
 *   inside the uploaded image.
 */
final class HomepageSectionContentFields
{
    /** Image + product + button text: the three banner types. */
    private const BANNER = ['image_path', 'product_id', 'button_text'];

    /** @var array<string, array<int, string>> */
    private const FIELDS = [
        'hero_slider' => self::BANNER,
        'top_small_banners' => self::BANNER,
        'offer_section' => self::BANNER,
        'blog_section' => ['image_path', 'title'],
        'service_section' => ['icon_key', 'title', 'subtitle'],
        'coupon_section' => ['logo_image_path', 'title', 'coupon_code', 'discount_text', 'validity_text'],
        'strip_offer_banner' => ['image_path'],
        'video_section' => ['video_source', 'video_url', 'video_file_path'],
        // Top Selling is a product row whose products are picked here instead
        // of on the Products form: row 1 is the Special Offer deal card, rows
        // 2-9 are the stack beside it. The countdown lives on the section.
        'top_selling_section' => ['product_id'],
    ];

    /**
     * The five Fastkart service sprites. The keys are the symbol ids inside
     * `public/fastkart-store/svg/service-icon-4.svg` - the other sprite file
     * names the same icons differently, so an icon must never be referenced
     * from `service-icon.svg` (that left four service boxes blank in item 38).
     *
     * @var array<string, string>
     */
    public const SERVICE_ICONS = [
        'shipping' => 'Free Shipping',
        'service' => '24 x 7 Service',
        'pay' => 'Online Payment',
        'offer' => 'Festival Offers',
        'return' => 'Original / Return',
    ];

    /** @var array<string, string> */
    public const VIDEO_SOURCES = [
        'url' => 'Video Link (YouTube etc.)',
        'upload' => 'Upload Video File',
    ];

    /** Fields that hold an uploaded image. */
    public const IMAGE_FIELDS = ['image_path', 'logo_image_path'];

    /** @return array<int, string> */
    public function for(ProductHomepageSection $section): array
    {
        return self::FIELDS[(string) $section->section_type] ?? [];
    }

    public function has(ProductHomepageSection $section, string $field): bool
    {
        return in_array($field, $this->for($section), true);
    }

    /**
     * Columns this section type writes. `video_source` is a form-only control
     * that chooses between the two video columns; it is never stored.
     *
     * @return array<int, string>
     */
    public function columns(ProductHomepageSection $section): array
    {
        return array_values(array_filter(
            $this->for($section),
            fn (string $field): bool => $field !== 'video_source'
        ));
    }

    /**
     * What one row is called in the editor. Top Selling's first row is the
     * Special Offer card, so it must not read "Row 1" like the rest.
     */
    public function rowLabel(ProductHomepageSection $section, ?int $rowNumber): string
    {
        if (! $section->usesContentRowProducts()) {
            return 'Row '.($rowNumber ?? '');
        }

        return $rowNumber === 1
            ? 'Special Offer (deal card)'
            : 'Product '.(($rowNumber ?? 1) - 1);
    }

    /** Human label for one row input. */
    public function label(string $field): string
    {
        return match ($field) {
            'image_path' => 'Banner Image',
            'logo_image_path' => 'Bank / Wallet Logo',
            'product_id' => 'Product',
            'button_text' => 'Button Text',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'coupon_code' => 'Coupon Code',
            'discount_text' => 'Discount Text',
            'validity_text' => 'Validity Text',
            'icon_key' => 'Icon',
            'video_source' => 'Video Source',
            'video_url' => 'Video Link',
            'video_file_path' => 'Video File',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }
}
