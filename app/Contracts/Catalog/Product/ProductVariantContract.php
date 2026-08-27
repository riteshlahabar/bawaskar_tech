<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

interface ProductVariantContract
{
    public function sync(Product $product, array $variants): void;

    public function formData(Product $product): array;
}