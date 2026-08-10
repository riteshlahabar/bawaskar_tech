<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductImage;
use App\Models\Inventory\InventoryBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    private ?string $primaryImagePath = null;

    private array $galleryImagePaths = [];

    private ?array $openingStockData = null;

    protected function rules(array $module, ?Model $record = null): array
    {
        $rules = parent::rules($module, $record);

        if ($record) {
            return $rules;
        }

        $stockSupportFields = [
            'opening_stock_warehouse_id',
            'opening_stock_batch_no',
            'opening_stock_manufacturing_date',
            'opening_stock_expiry_date',
            'opening_stock_purchase_price',
            'opening_stock_quantity',
            'opening_stock_reserved_quantity',
            'opening_stock_low_stock_alert',
        ];

        $rules['opening_stock_warehouse_id'][] = 'required_with:'.implode(',', array_diff($stockSupportFields, ['opening_stock_warehouse_id']));
        $rules['opening_stock_batch_no'][] = 'required_with:'.implode(',', array_diff($stockSupportFields, ['opening_stock_batch_no']));
        $rules['opening_stock_quantity'][] = 'required_with:'.implode(',', array_diff($stockSupportFields, ['opening_stock_quantity']));
        $rules['opening_stock_expiry_date'][] = 'after_or_equal:opening_stock_manufacturing_date';
        $rules['opening_stock_reserved_quantity'][] = 'lte:opening_stock_quantity';

        return $rules;
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $this->primaryImagePath = null;
        $this->galleryImagePaths = [];
        $this->openingStockData = null;

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

        $this->openingStockData = $this->extractOpeningStockData($data);
        $data = $this->normalizeHomepageSectionData($data, $module);

        if (blank($data['sort_order'] ?? null)) {
            $data['sort_order'] = 0;
        }

        if (blank($data['homepage_sort_order'] ?? null)) {
            $data['homepage_sort_order'] = 0;
        }

        return $data;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $isCreating = $record === null;
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

        if ($isCreating && $this->shouldCreateOpeningStock()) {
            InventoryBatch::query()->create(array_merge($this->openingStockData, [
                'product_id' => $product->getKey(),
            ]));
        }

        return $product->fresh(['category', 'brand', 'unit', 'images', 'homepageSection', 'inventoryBatches']);
    }

    protected function mutateValidatedDataBeforeSave(array $data): array
    {
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['sort_order'] === '') {
            $data['sort_order'] = 0;
        }

        return $data;
    }
    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);

        $primaryImage = $record->relationLoaded('images')
            ? $record->images->firstWhere('is_primary', true)
            : $record->images()->where('is_primary', true)->first();

        $data['primary_image'] = $primaryImage?->path;

        return $data;
    }

    private function extractOpeningStockData(array &$data): ?array
    {
        $fieldMap = [
            'opening_stock_warehouse_id' => 'warehouse_id',
            'opening_stock_batch_no' => 'batch_no',
            'opening_stock_manufacturing_date' => 'manufacturing_date',
            'opening_stock_expiry_date' => 'expiry_date',
            'opening_stock_purchase_price' => 'purchase_price',
            'opening_stock_quantity' => 'quantity',
            'opening_stock_reserved_quantity' => 'reserved_quantity',
            'opening_stock_low_stock_alert' => 'low_stock_alert',
        ];

        $stockInput = Arr::only($data, array_keys($fieldMap));
        $data = Arr::except($data, array_keys($fieldMap));

        $hasAnyValue = collect($stockInput)->contains(function ($value): bool {
            return ! blank($value);
        });

        if (! $hasAnyValue) {
            return null;
        }

        $stockData = [];

        foreach ($fieldMap as $inputKey => $stockKey) {
            $value = $stockInput[$inputKey] ?? null;

            if (in_array($stockKey, ['purchase_price', 'reserved_quantity', 'low_stock_alert'], true) && blank($value)) {
                $value = 0;
            }

            $stockData[$stockKey] = blank($value) ? null : $value;
        }

        return $stockData;
    }

    private function shouldCreateOpeningStock(): bool
    {
        if ($this->openingStockData === null) {
            return false;
        }

        return filled($this->openingStockData['warehouse_id'] ?? null)
            && filled($this->openingStockData['batch_no'] ?? null)
            && ($this->openingStockData['quantity'] ?? null) !== null;
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