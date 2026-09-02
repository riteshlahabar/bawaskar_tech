<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductImageContract;
use App\Contracts\Catalog\Product\ProductMediaContract;
use App\Contracts\Catalog\Product\ProductRepositoryContract;
use App\Contracts\Catalog\Product\ProductSkuContract;
use App\Contracts\Catalog\Product\ProductStockContract;
use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Contracts\Catalog\Product\ProductVariantContract;
use App\Contracts\Catalog\Product\ProductWorkflowContract;
use App\Data\Catalog\ProductSaveData;
use App\Models\Catalog\Product;

final class ProductWorkflowService implements ProductWorkflowContract
{
    public function __construct(
        private readonly ProductRepositoryContract $products,
        private readonly ProductImageContract $images,
        private readonly ProductStockContract $stock,
        private readonly ProductVariantContract $variants,
        private readonly ProductMediaContract $media,
        private readonly ProductTranslationContract $translations,
        private readonly ProductSkuContract $sku,
    ) {
    }

    public function save(ProductSaveData $data, ?Product $product = null): Product
    {
        $creating = $product === null;
        $attributes = $data->product;

        if ($creating && blank($attributes['sku'] ?? null)) {
            $attributes['sku'] = $this->sku->generate($attributes['name'] ?? null);
        }

        $product = $this->products->save($attributes, $product);

        $this->images->sync($product, $data->primaryImagePath, $data->galleryImagePaths, $data->removeGalleryImageIds);
        if ($creating) {
            $this->stock->createOpeningStock($product, $data->openingStock);
        }
        $this->variants->sync($product, $data->variants);
        $this->media->sync($product, $data->media);
        $this->translations->sync($product, $data->translations);

        return $this->products->fresh($product, [
            'category', 'brand', 'unit', 'images', 'media', 'homepageSection',
            'inventoryBatches', 'variants.inventoryBatches', 'translations',
        ]);
    }
}