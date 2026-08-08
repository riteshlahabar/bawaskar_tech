<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    private ?string $primaryImagePath = null;

    private array $galleryImagePaths = [];

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $this->primaryImagePath = null;
        $this->galleryImagePaths = [];

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

        return $this->normalizeHomepageSectionData($data, $module);
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

        $product = $product->fresh(['category', 'brand', 'unit', 'images', 'homepageSection']);

        return $product->fresh(['category', 'brand', 'unit', 'images', 'homepageSection']);
    }



    protected function mutateValidatedDataBeforeSave(array $data): array
    {
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['sort_order'] === '') {
            $data['sort_order'] = 0;
        }

        return $data;
    }

    private function normalizeHomepageSectionData(array $data, array $module): array
    {
        $conditionalFields = collect($module['fields'] ?? [])
            ->filter(fn (array $field): bool => ($field['visibility_field'] ?? null) === 'homepage_section_id' && ! empty($field['name']))
            ->keyBy('name');

        if ($conditionalFields->isEmpty()) {
            return $data;
        }

        $sectionId = $data['homepage_section_id'] ?? null;
        $section = $sectionId ? ProductHomepageSection::query()->find($sectionId) : null;
        $sectionType = (string) ($section?->section_type ?? '');
        $layoutType = (string) ($section?->layout_type ?? '');

        foreach ($conditionalFields as $fieldName => $field) {
            $showForSectionTypes = array_values(array_filter((array) ($field['show_for_section_types'] ?? [])));
            $showForLayoutTypes = array_values(array_filter((array) ($field['show_for_layout_types'] ?? [])));

            $shouldKeep = $section !== null;

            if ($shouldKeep && $showForSectionTypes !== []) {
                $shouldKeep = in_array($sectionType, $showForSectionTypes, true);
            }

            if ($shouldKeep && $showForLayoutTypes !== []) {
                $shouldKeep = in_array($layoutType, $showForLayoutTypes, true);
            }

            if (! $shouldKeep) {
                $data[$fieldName] = null;
            }
        }

        return $data;
    }
}