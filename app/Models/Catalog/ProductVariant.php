<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'group_name', 'value', 'price_difference', 'stock_quantity', 'is_default', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'price_difference' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}