<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontSectionProduct;
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

        unset($data['gallery_images']);

        if ($request->hasFile('gallery_images')) {
            foreach ((array) $request->file('gallery_images') as $file) {
                if ($file && $file->isValid()) {
                    $this->galleryImagePaths[] = $this->storePublicUpload($file, $module, [
                        'upload_dir' => 'uploads/products/gallery',
                    ]);
                }
            }
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

        if ($this->galleryImagePaths !== []) {
            $startOrder = (int) ProductImage::query()
                ->where('product_id', $product->getKey())
                ->max('sort_order');

            foreach ($this->galleryImagePaths as $index => $path) {
                ProductImage::query()->create([
                    'product_id' => $product->getKey(),
                    'path' => $path,
                    'is_primary' => false,
                    'sort_order' => $startOrder + $index + 1,
                ]);
            }
        }

        $product = $product->fresh(['category', 'brand', 'unit', 'images']);

        $this->syncHomepageDisplay($product);

        return $product->fresh(['category', 'brand', 'unit', 'images']);
    }

    private function syncHomepageDisplay(Product $product): void
    {
        $rows = config('homepage_rows.product_rows', []);
        $selectedRow = (string) ($product->storefront_row ?? '');
        $productUrl = route('store.product', ['product' => $product->getKey()], false);

        StorefrontBanner::query()
            ->where('button_url', $productUrl)
            ->delete();

        $productSectionKeys = collect($rows)
            ->where('type', 'product')
            ->pluck('section_key')
            ->filter()
            ->values();

        $sectionIds = StorefrontSection::query()
            ->whereIn('section_key', $productSectionKeys)
            ->pluck('id');

        if ($sectionIds->isNotEmpty()) {
            StorefrontSectionProduct::query()
                ->whereIn('section_id', $sectionIds)
                ->where('product_id', $product->getKey())
                ->delete();
        }

        if ($selectedRow === '' || ! isset($rows[$selectedRow])) {
            return;
        }

        $row = $rows[$selectedRow];

        if (($row['type'] ?? '') === 'banner') {
            $imagePath = $product->storefront_banner_image
                ?: optional($product->images->first())->path;

            if (! $imagePath) {
                return;
            }

            StorefrontBanner::query()->create([
                'placement' => $row['placement'],
                'title' => $product->storefront_title ?: $product->name,
                'subtitle' => $product->storefront_subtitle,
                'description' => $product->storefront_description ?: $product->description,
                'button_text' => 'Shop Now',
                'button_url' => $productUrl,
                'image_path' => $imagePath,
                'sort_order' => (int) ($product->sort_order ?? 0),
                'is_active' => (bool) $product->is_active,
            ]);

            return;
        }

        if (($row['type'] ?? '') === 'product') {
            $section = StorefrontSection::query()->updateOrCreate(
                ['section_key' => $row['section_key']],
                [
                    'title' => $row['title'],
                    'section_type' => 'product',
                    'source_type' => 'manual',
                    'category_id' => null,
                    'product_limit' => 24,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_active' => true,
                ]
            );

            StorefrontSectionProduct::query()->updateOrCreate(
                [
                    'section_id' => $section->getKey(),
                    'product_id' => $product->getKey(),
                ],
                [
                    'sort_order' => (int) ($product->sort_order ?? 0),
                    'is_active' => (bool) $product->is_active,
                ]
            );
        }
    }

    protected function mutateValidatedDataBeforeSave(array $data): array
    {
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['sort_order'] === '') {
            $data['sort_order'] = 0;
        }

        return $data;
    }
}