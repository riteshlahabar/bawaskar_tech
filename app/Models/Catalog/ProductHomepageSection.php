<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductHomepageSection extends Model
{
    /**
     * Section types whose content is banners/text rows, edited from
     * Homepage Settings -> Add Content. A product never feeds one of these:
     * the content row points AT a product instead, through its own product_id.
     */
    public const CONTENT_SECTION_TYPES = [
        'hero_slider',
        'top_small_banners',
        'coupon_section',
        'offer_section',
        'strip_offer_banner',
        'service_section',
        'blog_section',
        'video_section',
    ];

    /** Section types that render product cards rather than content rows. */
    public const PRODUCT_SECTION_TYPES = [
        'product_section',
        'top_selling_section',
    ];

    /**
     * Product sections whose products are PICKED IN THE CONTENT EDITOR, one
     * product per row, exactly like a banner row points at a product.
     *
     * Top Selling used to be built from the `products.is_top_selling` flag on
     * the Products form, with the deal card chosen by a second flag. Both flags
     * are gone: row 1 of the content editor is the Special Offer deal card and
     * rows 2-9 are the product stack, and the countdown now lives on the
     * section itself (`deal_starts_at` / `deal_ends_at` / `deal_timer_active`).
     */
    public const ROW_PRODUCT_SECTION_TYPES = [
        'top_selling_section',
    ];

    /**
     * The only section types the Products form's Homepage Section Title
     * dropdown may offer - a product can be placed on these and nothing else.
     */
    public const ASSIGNABLE_SECTION_TYPES = [
        'product_section',
    ];

    /**
     * How many content rows the editor offers per section type.
     * null = unlimited, so the + button is shown.
     *
     * Keyed on SECTION TYPE, never on layout_type: live "Awards" (27) is a
     * blog_section sitting on a product_slider layout and holds 6 rows, and
     * "Store Services" (24) is a service_section with a NULL layout holding 5
     * - a layout-keyed limit would hide their rows.
     */
    public const CONTENT_ROW_LIMITS = [
        'hero_slider' => null,
        'top_small_banners' => null,
        'blog_section' => null,
        'coupon_section' => null,
        'offer_section' => 2,
        'video_section' => 2,
        'service_section' => 5,
        'strip_offer_banner' => 1,
        // 1 deal card + the 8-product stack the Fastkart layout renders.
        'top_selling_section' => 9,
    ];

    /**
     * Which layouts each section type may use, so Layout Type offers only the
     * layouts that belong to the chosen Section Type.
     *
     * The layout only really changes rendering for two types - `offer_section`
     * (the banner shape, which also sets its content row count) and
     * `video_section` (both players, or player + text). Everywhere else it is
     * a label, so the type gets its one natural layout.
     *
     * Two entries carry a second layout purely because a LIVE row already uses
     * it: Top Selling (21) is `product_grid` and Awards (27) is a
     * `blog_section` on `product_slider`. Dropping those would make an
     * existing row impossible to save.
     */
    public const LAYOUTS_BY_SECTION_TYPE = [
        'hero_slider' => ['full_width_slider'],
        'top_small_banners' => ['four_banner_slider'],
        'category_section' => ['category_slider'],
        'product_section' => ['product_slider', 'product_grid'],
        'coupon_section' => ['coupon_slider'],
        'top_selling_section' => ['products_with_offer', 'product_grid'],
        'offer_section' => ['two_column_banner', 'big_small_banner', 'full_width_banner'],
        'strip_offer_banner' => ['text_strip'],
        'service_section' => ['service_icons'],
        'blog_section' => ['blog_slider', 'product_slider'],
        'video_section' => ['two_videos', 'video_text'],
    ];

    /**
     * The layouts a section type may use, or every layout when the type is
     * unknown - a blank Section Type must not leave the dropdown empty.
     *
     * @return array<int, string>
     */
    public static function layoutsFor(?string $sectionType): array
    {
        return self::LAYOUTS_BY_SECTION_TYPE[(string) $sectionType] ?? [];
    }

    /** `category_section` is deliberately in neither type list. */
    protected $fillable = [
        'section_key', 'title', 'subtitle', 'section_type', 'layout_type', 'source_type', 'category_id',
        'product_limit', 'item_limit', 'image_size_note', 'sort_order', 'start_at', 'end_at', 'settings', 'is_active',
        'deal_starts_at', 'deal_ends_at', 'deal_timer_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'product_limit' => 'integer',
            'item_limit' => 'integer',
            'sort_order' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
            'deal_starts_at' => 'datetime',
            'deal_ends_at' => 'datetime',
            'deal_timer_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductHomepageSectionItem::class, 'section_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Products placed on this homepage row. The column is `nullOnDelete`, so
     * the database would happily unlink them if the row were deleted - the
     * admin refuses that instead and asks for a transfer first
     * (HomepageSectionProductsContract).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'homepage_section_id')->orderBy('sort_order')->orderBy('id');
    }

    public function isContentSection(): bool
    {
        return in_array((string) $this->section_type, self::CONTENT_SECTION_TYPES, true);
    }

    public function isProductSection(): bool
    {
        return in_array((string) $this->section_type, self::PRODUCT_SECTION_TYPES, true);
    }

    /** Does this row take its products from its own content rows? */
    public function usesContentRowProducts(): bool
    {
        return in_array((string) $this->section_type, self::ROW_PRODUCT_SECTION_TYPES, true);
    }

    /**
     * Rows whose content is edited from Add / Edit Content: every content
     * section, plus a product row that picks its products there.
     */
    public function hasContentEditor(): bool
    {
        return $this->isContentSection() || $this->usesContentRowProducts();
    }

    /** May a product be placed on this row from the Products form? */
    public function isAssignableFromProductForm(): bool
    {
        return in_array((string) $this->section_type, self::ASSIGNABLE_SECTION_TYPES, true);
    }

    /**
     * Is the Special Offer countdown live? The deal card is still shown
     * without it - only the timer block is conditional.
     */
    public function dealTimerRunning(): bool
    {
        if (! $this->deal_timer_active || ! $this->deal_ends_at) {
            return false;
        }

        if ($this->deal_starts_at && $this->deal_starts_at->isFuture()) {
            return false;
        }

        return $this->deal_ends_at->isFuture();
    }

    /**
     * Rows the content editor offers, or null for unlimited.
     *
     * `offer_section` is the one type whose count really is a layout question:
     * a two column / big-small banner shows 2, a full width banner shows 1.
     * Everything else reads CONTENT_ROW_LIMITS.
     */
    public function contentRowLimit(): ?int
    {
        if ((string) $this->section_type === 'offer_section') {
            return (string) $this->layout_type === 'full_width_banner' ? 1 : 2;
        }

        return self::CONTENT_ROW_LIMITS[(string) $this->section_type] ?? null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }

    protected static function booted(): void
    {
        static::creating(function (self $section): void {
            if (blank($section->section_key)) {
                $base = Str::slug((string) ($section->title ?: $section->section_type ?: 'section'));
                $base = $base ?: 'section';
                $section->section_key = $base.'-'.time().'-'.random_int(100, 999);
            }
        });

        static::updating(function (self $section): void {
            if (blank($section->section_key)) {
                $base = Str::slug((string) ($section->title ?: $section->section_type ?: 'section'));
                $base = $base ?: 'section';
                $section->section_key = $base.'-'.time().'-'.random_int(100, 999);
            }
        });
    }

    public function getSectionTypeNameAttribute(): string
    {
        return trim(ucwords(str_replace('_', ' ', (string) $this->section_type)));
    }

    public function getLayoutTypeNameAttribute(): string
    {
        return trim(ucwords(str_replace('_', ' ', (string) $this->layout_type)));
    }
}
