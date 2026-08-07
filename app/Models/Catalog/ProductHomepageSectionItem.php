<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductHomepageSectionItem extends Model
{
    protected $fillable = [
        'section_id','slot','title','subtitle','description','highlight_text','coupon_code','validity_text','button_text','button_url',
        'image_path','mobile_image_path','logo_image_path','icon_key','price','old_price','sold_quantity','total_quantity','timer_end_at',
        'background_color','text_color','sort_order','settings','is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'sold_quantity' => 'integer',
            'total_quantity' => 'integer',
            'timer_end_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductHomepageSection::class, 'section_id');
    }
}