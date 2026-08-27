<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductStockContract;
use App\Contracts\Catalog\Product\ProductVariantContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

final class ProductVariantService implements ProductVariantContract
{
    public function __construct(private readonly ProductStockContract $stock)
    {
    }

    public function sync(Product $product, array $variants): void
    {
        $keptIds = [];
        $firstActive = null;
        $mainVariant = null;

        foreach ($variants as $index => $row) {
            $sizeValue = (float) ($row['size_value'] ?? 0);
            if ($sizeValue <= 0 || blank($row['size_unit'] ?? null)) {
                continue;
            }

            $variantId = (int) ($row['id'] ?? 0);
            $variant = $variantId > 0
                ? $product->variants()->whereKey($variantId)->firstOrFail()
                : new ProductVariant(['product_id' => $product->id]);

            $displayValue = rtrim(rtrim(number_format($sizeValue, 3, '.', ''), '0'), '.').' '.strtoupper((string) $row['size_unit']);
            $variant->fill([
                'group_name' => 'Packing Size',
                'value' => $displayValue,
                'size_value' => $sizeValue,
                'size_unit' => strtoupper((string) $row['size_unit']),
                'variant_sku' => filled($row['variant_sku'] ?? null) ? trim((string) $row['variant_sku']) : null,
                'units_per_case' => max(1, (int) ($row['units_per_case'] ?? 1)),
                'mrp' => (float) ($row['mrp'] ?? 0),
                'dealer_price' => (float) ($row['dealer_price'] ?? 0),
                'customer_price' => (float) ($row['customer_price'] ?? 0),
                'price_difference' => 0,
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'is_default' => filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOL),
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ]);
            $variant->save();

            $keptIds[] = $variant->id;
            $firstActive ??= $variant->is_active ? $variant : null;
            $mainVariant ??= ($variant->is_active && $variant->is_default) ? $variant : null;
            $this->stock->syncVariantOpeningStock($product, $variant, $row);
        }

        $product->variants()->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))->update(['is_active' => false, 'is_default' => false]);
        if ($keptIds === []) {
            $product->variants()->update(['is_active' => false, 'is_default' => false]);
            return;
        }

        $mainVariant ??= $firstActive;
        if ($mainVariant) {
            $product->variants()->where('id', '<>', $mainVariant->id)->update(['is_default' => false]);
            $mainVariant->forceFill(['is_default' => true])->save();
            $product->forceFill([
                'mrp' => $mainVariant->mrp,
                'dealer_price' => $mainVariant->dealer_price,
                'customer_price' => $mainVariant->customer_price,
            ])->save();
        }
    }

    public function formData(Product $product): array
    {
        $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->with('inventoryBatches')->get();

        return $variants->where('is_active', true)->map(function (ProductVariant $variant): array {
            preg_match('/^([0-9.]+)\s*([A-Za-z]+)?/', (string) $variant->value, $legacySize);
            return [
                'id' => $variant->id,
                'size_value' => $variant->size_value ?: ($legacySize[1] ?? null),
                'size_unit' => $variant->size_unit ?: strtoupper((string) ($legacySize[2] ?? '')),
                'variant_sku' => $variant->variant_sku,
                'units_per_case' => $variant->units_per_case ?: 1,
                'mrp' => $variant->mrp ?? $variant->product?->mrp,
                'dealer_price' => $variant->dealer_price ?? $variant->product?->dealer_price,
                'customer_price' => $variant->customer_price ?? $variant->product?->customer_price,
                'sort_order' => $variant->sort_order,
                'is_default' => $variant->is_default,
                'is_active' => $variant->is_active,
                'purchase_price' => 0,
                'low_stock_alert' => 0,
            ];
        })->values()->all();
    }
}