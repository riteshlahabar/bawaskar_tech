<?php

namespace App\Models\Catalog;

use App\Models\Inventory\InventoryBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
        'sku',
        'name',
        'product_type',
        'storefront_row',
        'hsn_code',
        'gst_percent',
        'mrp',
        'customer_price',
        'dealer_price',
        'description',
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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
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
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
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
}