<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductImage;
use App\Models\Catalog\ProductTranslation;
use App\Models\Inventory\InventoryBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    private const TRANSLATION_LOCALES = ['hi', 'mr', 'gu', 'kn', 'te'];

    private ?string $primaryImagePath = null;

    private array $galleryImagePaths = [];

    private array $removeGalleryImageIds = [];

    private ?array $openingStockData = null;

    private array $translationData = [];

    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $translations = [];

        foreach (self::TRANSLATION_LOCALES as $locale) {
            $translations[$locale] = [
                'name' => $this->translateText($validated['name'], $locale),
                'description' => filled($validated['description'] ?? null)
                    ? $this->translateText((string) $validated['description'], $locale)
                    : '',
            ];
        }

        return response()->json(['translations' => $translations]);
    }
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
        $this->translationData = [];

        $data = parent::prepareData($validated, $request, $module);

        if (array_key_exists('primary_image', $data)) {
            $this->primaryImagePath = $data['primary_image'];
            unset($data['primary_image']);
        }

        unset($data['gallery_images']);

        $this->removeGalleryImageIds = collect((array) $request->input('remove_gallery_image_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

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
        $this->translationData = $this->extractTranslationData($data);
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

        if ($this->removeGalleryImageIds !== []) {
            $galleryImagesToRemove = ProductImage::query()
                ->where('product_id', $product->getKey())
                ->where('is_primary', false)
                ->whereIn('id', $this->removeGalleryImageIds)
                ->get();

            foreach ($galleryImagesToRemove as $galleryImage) {
                $absolutePath = filled($galleryImage->path) ? public_path($galleryImage->path) : null;
                $galleryImage->delete();

                if ($absolutePath && is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }
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

        $this->syncTranslations($product);

        return $product->fresh(['category', 'brand', 'unit', 'images', 'homepageSection', 'inventoryBatches', 'translations']);
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

        $translations = $record->relationLoaded('translations')
            ? $record->translations
            : $record->translations()->get();

        foreach (self::TRANSLATION_LOCALES as $locale) {
            $translation = $translations->firstWhere('locale', $locale);
            $data['translation_'.$locale.'_name'] = $translation?->name;
            $data['translation_'.$locale.'_description'] = $translation?->description;
        }

        return $data;
    }

    private function translateText(string $text, string $locale): string
    {
        $response = Http::timeout(12)
            ->retry(1, 250)
            ->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'en',
                'tl' => $locale,
                'dt' => 't',
                'q' => $text,
            ]);

        if (! $response->successful()) {
            abort(422, 'Auto translation failed. Please enter translations manually.');
        }

        $segments = $response->json()[0] ?? [];

        return collect($segments)
            ->map(fn ($segment): string => (string) ($segment[0] ?? ''))
            ->implode('');
    }
    private function extractTranslationData(array &$data): array
    {
        $translations = [];

        foreach (self::TRANSLATION_LOCALES as $locale) {
            $nameKey = 'translation_'.$locale.'_name';
            $descriptionKey = 'translation_'.$locale.'_description';

            $translations[$locale] = [
                'name' => trim((string) ($data[$nameKey] ?? '')),
                'description' => trim((string) ($data[$descriptionKey] ?? '')),
            ];

            unset($data[$nameKey], $data[$descriptionKey]);
        }

        return $translations;
    }

    private function syncTranslations(Product $product): void
    {
        foreach ($this->translationData as $locale => $translation) {
            $name = $translation['name'] ?? '';
            $description = $translation['description'] ?? '';

            if ($name === '' && $description === '') {
                ProductTranslation::query()
                    ->where('product_id', $product->getKey())
                    ->where('locale', $locale)
                    ->delete();

                continue;
            }

            ProductTranslation::query()->updateOrCreate(
                ['product_id' => $product->getKey(), 'locale' => $locale],
                [
                    'name' => $name !== '' ? $name : $product->name,
                    'description' => $description !== '' ? $description : null,
                ]
            );
        }
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




