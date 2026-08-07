<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductHomepageSection extends Model
{
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
}