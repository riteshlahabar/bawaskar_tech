<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    private ?string $primaryImagePath = null;

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        if (array_key_exists('primary_image', $data)) {
            $this->primaryImagePath = $data['primary_image'];
            unset($data['primary_image']);
        }

        return $data;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $product = parent::persist($data, $record);

        if ($this->primaryImagePath) {
            ProductImage::query()
                ->where('product_id', $product->getKey())
                ->update(['is_primary' => false]);

            ProductImage::query()->create([
                'product_id' => $product->getKey(),
                'path' => $this->primaryImagePath,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        return $product->fresh(['category', 'brand', 'unit', 'images']);
    }
}