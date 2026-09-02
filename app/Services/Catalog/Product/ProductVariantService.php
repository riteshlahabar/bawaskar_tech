<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductStockContract;
use App\Contracts\Catalog\Product\ProductVariantContract;
use App\Contracts\Catalog\Product\ProductVariantProjectionContract;
use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

final class ProductVariantService implements ProductVariantContract
{
    public function __construct(
        private readonly ProductStockContract $stock,
        private readonly ProductVariantUnitContract $units,
        private readonly ProductVariantProjectionContract $projection,
    ) {
    }

    public function sync(Product $product, array $variants): void
    {
        $keptIds = [];
        $firstActive = null;
        $mainVariant = null;

        foreach ($variants as $index => $row) {
            $sizeValue = (float) ($row['size_value'] ?? 0);
            $unitId = filled($row['unit_id'] ?? null) ? (int) $row['unit_id'] : null;
            $sizeUnit = $this->units->shortNameFor($unitId) ?? strtoupper(trim((string) ($row['size_unit'] ?? '')));

            if ($sizeValue <= 0 || blank($sizeUnit)) {
                continue;
            }

            $variantId = (int) ($row['id'] ?? 0);
            $variant = $variantId > 0
                ? $product->variants()->whereKey($variantId)->firstOrFail()
                : new ProductVariant(['product_id' => $product->id]);

            $displayValue = rtrim(rtrim(number_format($sizeValue, 3, '.', ''), '0'), '.').' '.$sizeUnit;
            $variant->fill([
                'group_name' => 'Packing Size',
                'value' => $displayValue,
                'unit_id' => $unitId,
                'size_value' => $sizeValue,
                'size_unit' => $sizeUnit,
                'variant_sku' => filled($row['variant_sku'] ?? null) ? trim((string) $row['variant_sku']) : null,
                'hsn_code' => filled($row['hsn_code'] ?? null) ? trim((string) $row['hsn_code']) : null,
                'gst_percent' => (float) ($row['gst_percent'] ?? 0),
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
            $this->mirrorMainVariantToProduct($product, $mainVariant);
        }
    }

    /**
     * Price, unit, SKU, HSN and GST are now entered per variant, but orders,
     * invoices, the cart totals and the product listing still read the product
     * level columns. Mirroring the main variant keeps those consumers correct
     * without rewriting every sales service.
     */
    private function mirrorMainVariantToProduct(Product $product, ProductVariant $variant): void
    {
        $projected = $this->projection->fromVariant($variant);

        $mirrored = [
            'mrp' => $projected['mrp'],
            'dealer_price' => $projected['dealer_price'],
            'customer_price' => $projected['customer_price'],
            'gst_percent' => $projected['gst_percent'],
            'unit_id' => $projected['unit_id'] ?? $product->unit_id,
        ];

        // products.sku is unique and identifies the product itself, so only the
        // HSN code follows the variant here.
        if (filled($projected['hsn_code'])) {
            $mirrored['hsn_code'] = $projected['hsn_code'];
        }

        $product->forceFill($mirrored)->save();
    }

    public function formData(Product $product): array
    {
        $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->with('inventoryBatches')->get();

        $active = $variants->where('is_active', true);

        // Fall back to the inactive rows rather than showing an empty repeater,
        // otherwise reopening such a product hides the prices it already has.
        $rows = $this->rows($active->isNotEmpty() ? $active : $variants, $product);

        return $rows !== [] ? $rows : $this->rowFromProduct($product);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ProductVariant>  $variants
     * @return array<int, array<string, mixed>>
     */
    private function rows($variants, Product $product): array
    {
        return $variants->map(function (ProductVariant $variant) use ($product): array {
            preg_match('/^([0-9.]+)\s*([A-Za-z]+)?/', (string) $variant->value, $legacySize);
            $sizeUnit = $variant->size_unit ?: strtoupper((string) ($legacySize[2] ?? ''));

            return [
                'id' => $variant->id,
                'unit_id' => $variant->unit_id ?? $this->units->idForShortName($sizeUnit),
                'size_value' => $variant->size_value ?: ($legacySize[1] ?? null),
                'size_unit' => $sizeUnit,
                'variant_sku' => $variant->variant_sku,
                // Products created before HSN/GST moved into the variant keep
                // those values at product level, so fall back to them and the
                // next save carries them onto the variant.
                'hsn_code' => $variant->hsn_code ?: $product->hsn_code,
                'gst_percent' => $variant->gst_percent ?? $product->gst_percent,
                'units_per_case' => $variant->units_per_case ?: 1,
                'mrp' => $variant->mrp ?? $product->mrp,
                'dealer_price' => $variant->dealer_price ?? $product->dealer_price,
                'customer_price' => $variant->customer_price ?? $product->customer_price,
                'sort_order' => $variant->sort_order,
                'is_default' => $variant->is_default,
                'is_active' => $variant->is_active,
                'purchase_price' => 0,
                'low_stock_alert' => 0,
            ];
        })->values()->all();
    }

    /**
     * Products added before packing variants existed keep their price, unit,
     * SKU, HSN and GST on the product row itself. Seeding the first variant
     * with those values means opening such a product for edit shows them
     * instead of an empty row. Pack size is deliberately left blank so the
     * admin enters the real size rather than inheriting a guess.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rowFromProduct(Product $product): array
    {
        if (blank($product->getKey())) {
            return [];
        }

        return [[
            'id' => null,
            'unit_id' => $product->unit_id,
            'size_value' => null,
            'size_unit' => $this->units->shortNameFor($product->unit_id),
            'variant_sku' => $product->sku,
            'hsn_code' => $product->hsn_code,
            'gst_percent' => $product->gst_percent,
            'units_per_case' => 1,
            'mrp' => $product->mrp,
            'dealer_price' => $product->dealer_price,
            'customer_price' => $product->customer_price,
            'sort_order' => 0,
            'is_default' => true,
            'is_active' => true,
            'purchase_price' => 0,
            'low_stock_alert' => 0,
        ]];
    }
}