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

    /** Section types fed by products assigned on the Products form. */
    public const PRODUCT_SECTION_TYPES = [
        'product_section',
        'top_selling_section',
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
    ];

    /** `category_section` is deliberately in neither type list. */
    protected $fillable = [
        'section_key', 'title', 'subtitle', 'section_type', 'layout_type', 'source_type', 'category_id',
        'product_limit', 'item_limit', 'image_size_note', 'sort_order', 'start_at', 'end_at', 'settings', 'is_active',
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
