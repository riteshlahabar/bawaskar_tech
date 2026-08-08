<?php

namespace App\Models\Catalog;

use App\Models\Inventory\InventoryBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'product_type_id',
        'unit_id',
        'sku',
        'batch_no',
        'expiry_date',
        'name',
        'product_type',
        'storefront_row',
        'hsn_code',
        'gst_percent',
        'mrp',
        'customer_price',
        'dealer_price',
        'description',
        'short_description',
        'detail_banner_image',
        'detail_banner_url',
        'detail_sidebar_banner_image',
        'detail_sidebar_banner_url',
        'seller_name',
        'seller_logo',
        'seller_description',
        'seller_address',
        'seller_contact',
        'manufacturer_title',
        'manufacturer_description',
        'sale_badge_text',
        'sold_quantity',
        'total_quantity',
        'low_stock_text',
        'is_top_selling',
        'is_trending',
        'is_new_arrival',
        'is_deal_timer_product',
        'storefront_title',
        'storefront_subtitle',
        'storefront_description',
        'storefront_banner_image',
        'additional_info',
        'care_instructions',
        'manufacturer_details',
        'is_offer_active',
        'offer_start_at',
        'offer_end_at',
        'is_visible_to_customers',
        'is_visible_to_dealers',
        'is_featured',
        'sort_order',
        'is_offer_product',
        'show_on_homepage',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'homepage_section_id',
        'homepage_title',
        'homepage_subtitle',
        'homepage_description',
        'homepage_image_path',
        'homepage_mobile_image_path',
        'homepage_logo_image_path',
        'homepage_offer_image_path',
        'homepage_highlight_text',
        'homepage_discount_text',
        'homepage_validity_text',
        'homepage_coupon_code',
        'homepage_button_text',
        'homepage_button_url',
        'homepage_icon_key',
        'homepage_slot',
        'homepage_background_color',
        'homepage_text_color',
        'homepage_sort_order',
    ];

    protected $casts = [
        'gst_percent' => 'decimal:2',
        'mrp' => 'decimal:2',
        'customer_price' => 'decimal:2',
        'dealer_price' => 'decimal:2',
        'is_offer_active' => 'boolean',
        'offer_start_at' => 'datetime',
        'offer_end_at' => 'datetime',
        'is_visible_to_customers' => 'boolean',
        'is_visible_to_dealers' => 'boolean',
        'is_featured' => 'boolean',
        'sold_quantity' => 'integer',
        'total_quantity' => 'integer',
        'is_top_selling' => 'boolean',
        'is_trending' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_deal_timer_product' => 'boolean',
        'is_offer_product' => 'boolean',
        'show_on_homepage' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'homepage_section_id' => 'integer',
        'homepage_sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function relatedProductLinks(): HasMany
    {
        return $this->hasMany(ProductRelatedProduct::class);
    }

    public function scopeVisibleFor(Builder $query, string $audience): Builder
    {
        $column = $audience === 'dealer' ? 'is_visible_to_dealers' : 'is_visible_to_customers';

        return $query->where('is_active', true)->where($column, true);
    }

    public function scopeStorefrontOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->latest('id');
    }

    public function getAvailableStockAttribute(): float
    {
        if (! $this->relationLoaded('inventoryBatches')) {
            return 0.0;
        }

        return (float) $this->inventoryBatches
            ->filter(fn (InventoryBatch $batch): bool => ! $batch->expiry_date || $batch->expiry_date->endOfDay()->isFuture())
            ->sum(fn (InventoryBatch $batch): float => max(0, (float) $batch->quantity - (float) $batch->reserved_quantity));
    }

    public function homepageSection(): BelongsTo
    {
        return $this->belongsTo(ProductHomepageSection::class, 'homepage_section_id');
    }

    public function getStorefrontImageUrlAttribute(): string
    {
        $productImageUrl = optional($this->images->first())->url;
        if (filled($productImageUrl)) {
            return $productImageUrl;
        }

        foreach ([$this->homepage_image_path, $this->storefront_banner_image] as $path) {
            if (filled($path)) {
                return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
            }
        }

        return asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    }

    public function getStorefrontDealImageUrlAttribute(): string
    {
        $productImageUrl = optional($this->images->first())->url;
        if (filled($productImageUrl)) {
            return $productImageUrl;
        }

        foreach ([$this->homepage_offer_image_path, $this->homepage_image_path, $this->storefront_banner_image] as $path) {
            if (filled($path)) {
                return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
            }
        }

        return asset('fastkart-store/images/grocery/deal/big.png');
    }
}
