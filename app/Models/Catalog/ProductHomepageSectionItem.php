<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductHomepageSectionItem extends Model
{
    protected $fillable = [
        'section_id', 'slot', 'title', 'subtitle', 'description', 'highlight_text', 'discount_text', 'validity_text',
        'coupon_code', 'button_text', 'button_url', 'image_path', 'mobile_image_path', 'logo_image_path', 'icon_key',
        'background_color', 'text_color', 'sort_order', 'settings', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductHomepageSection::class, 'section_id');
    }
}