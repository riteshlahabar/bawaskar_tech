<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductImage;
use App\Models\Catalog\ProductMedia;
use App\Models\Catalog\ProductTranslation;
use App\Models\Catalog\ProductType;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    private const TRANSLATION_LOCALES = ['hi', 'mr', 'gu', 'kn', 'te'];

    private ?string $primaryImagePath = null;

    private array $galleryImagePaths = [];

    private array $removeGalleryImageIds = [];

    private ?array $openingStockData = null;

    private array $translationData = [];

    private array $variantData = [];

    private array $mediaData = [];

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

        $rules = array_merge($rules, [
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.size_value' => ['required', 'numeric', 'min:0.001'],
            'variants.*.size_unit' => ['required', 'in:ML,LTR,GM,KG,PCS'],
            'variants.*.variant_sku' => ['nullable', 'string', 'max:100', 'distinct'],
            'variants.*.units_per_case' => ['required', 'integer', 'min:1'],
            'variants.*.mrp' => ['required', 'numeric', 'min:0'],
            'variants.*.dealer_price' => ['required', 'numeric', 'min:0'],
            'variants.*.customer_price' => ['required', 'numeric', 'min:0'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*.warehouse_id' => ['nullable', 'required_with:variants.*.opening_stock_quantity', 'exists:warehouses,id'],
            'variants.*.batch_no' => ['nullable', 'required_with:variants.*.opening_stock_quantity', 'string', 'max:80'],
            'variants.*.manufacturing_date' => ['nullable', 'date'],
            'variants.*.expiry_date' => ['nullable', 'date'],
            'variants.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.opening_stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'variants.*.low_stock_alert' => ['nullable', 'numeric', 'min:0'],
            'media' => ['nullable', 'array'],
            'media.*.id' => ['nullable', 'integer', 'exists:product_media,id'],
            'media.*.source_type' => ['required', 'in:upload,youtube'],
            'media.*.file' => ['nullable', 'file', 'mimes:mp4,webm', 'max:51200'],
            'media.*.youtube_url' => ['nullable', 'url', 'max:2048'],
            'media.*.title' => ['nullable', 'string', 'max:255'],
            'media.*.thumbnail' => ['nullable', 'image', 'max:5120'],
            'media.*.existing_file_path' => ['nullable', 'string', 'max:2048'],
            'media.*.existing_thumbnail_path' => ['nullable', 'string', 'max:2048'],
            'media.*.language' => ['nullable', 'string', 'max:10'],
            'media.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'media.*.is_active' => ['nullable', 'boolean'],
        ]);

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
        $this->variantData = [];
        $this->mediaData = [];

        $data = parent::prepareData($validated, $request, $module);

        $this->variantData = (array) ($data['variants'] ?? []);
        $this->mediaData = (array) ($data['media'] ?? []);
        unset($data['variants'], $data['media']);

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

        if (filled($data['product_type_id'] ?? null)) {
            $data['product_type'] = ProductType::query()->whereKey($data['product_type_id'])->value('slug');
        } else {
            $data['product_type'] = null;
        }

        foreach (['mrp', 'dealer_price', 'customer_price'] as $priceField) {
            if (blank($data[$priceField] ?? null)) {
                $data[$priceField] = 0;
            }
        }

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

        $this->syncVariants($product);
        $this->syncMedia($product, $this->module());

        $this->syncTranslations($product);

        return $product->fresh(['category', 'brand', 'unit', 'images', 'media', 'homepageSection', 'inventoryBatches', 'variants.inventoryBatches', 'translations']);
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
        $data['primary_image_id'] = $primaryImage?->getKey();

        $translations = $record->relationLoaded('translations')
            ? $record->translations
            : $record->translations()->get();

        foreach (self::TRANSLATION_LOCALES as $locale) {
            $translation = $translations->firstWhere('locale', $locale);
            $data['translation_'.$locale.'_name'] = $translation?->name;
            $data['translation_'.$locale.'_description'] = $translation?->description;
        }

        $variants = $record->relationLoaded('variants') ? $record->variants : $record->variants()->with('inventoryBatches')->get();
        $data['variants'] = $variants->where('is_active', true)->map(function (ProductVariant $variant): array {
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

        $media = $record->relationLoaded('media') ? $record->media : $record->media()->get();
        $data['media'] = $media->where('is_active', true)->map(fn (ProductMedia $item): array => [
            'id' => $item->id,
            'source_type' => $item->source_type,
            'file_path' => $item->file_path,
            'youtube_url' => $item->youtube_url,
            'title' => $item->title,
            'thumbnail_path' => $item->thumbnail_path,
            'language' => $item->language,
            'sort_order' => $item->sort_order,
            'is_active' => $item->is_active,
        ])->values()->all();

        return $data;
    }

    protected function formOptions(array $module): array
    {
        $options = parent::formOptions($module);
        $options['variant_warehouses'] = Warehouse::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();

        return $options;
    }

    private function syncVariants(Product $product): void
    {
        $keptIds = [];
        $firstActive = null;
        $mainVariant = null;

        foreach ($this->variantData as $index => $row) {
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
            if ($variant->is_active && ! $firstActive) {
                $firstActive = $variant;
            }
            if ($variant->is_active && $variant->is_default && ! $mainVariant) {
                $mainVariant = $variant;
            }

            $this->syncVariantOpeningStock($product, $variant, $row);
        }

        $product->variants()->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))->update([
            'is_active' => false,
            'is_default' => false,
        ]);

        if ($keptIds === []) {
            $product->variants()->update(['is_active' => false, 'is_default' => false]);
            return;
        }

        $mainVariant = $mainVariant ?: $firstActive;
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

    private function syncVariantOpeningStock(Product $product, ProductVariant $variant, array $row): void
    {
        if (blank($row['warehouse_id'] ?? null) || blank($row['batch_no'] ?? null) || ($row['opening_stock_quantity'] ?? null) === null || $row['opening_stock_quantity'] === '') {
            return;
        }

        $batch = InventoryBatch::query()->firstOrNew([
            'warehouse_id' => (int) $row['warehouse_id'],
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'batch_no' => trim((string) $row['batch_no']),
        ]);
        $batch->fill([
            'manufacturing_date' => $row['manufacturing_date'] ?? null,
            'expiry_date' => $row['expiry_date'] ?? null,
            'purchase_price' => (float) ($row['purchase_price'] ?? 0),
            'quantity' => (float) $row['opening_stock_quantity'],
            'low_stock_alert' => (float) ($row['low_stock_alert'] ?? 0),
        ]);
        if (! $batch->exists) {
            $batch->reserved_quantity = 0;
        }
        $batch->save();

        $variant->forceFill(['stock_quantity' => $variant->inventoryBatches()->sum('quantity')])->save();
    }

    private function syncMedia(Product $product, array $module): void
    {
        $keptIds = [];

        foreach ($this->mediaData as $index => $row) {
            $sourceType = (string) ($row['source_type'] ?? 'upload');
            $mediaId = (int) ($row['id'] ?? 0);
            $media = $mediaId > 0 ? $product->media()->whereKey($mediaId)->firstOrFail() : new ProductMedia(['product_id' => $product->id]);
            $file = request()->file('media.'.$index.'.file');
            $thumbnail = request()->file('media.'.$index.'.thumbnail');
            $filePath = $mediaId > 0 ? $media->file_path : null;
            $thumbnailPath = $mediaId > 0 ? $media->thumbnail_path : null;

            if ($file && $file->isValid()) {
                $filePath = $this->storePublicUpload($file, $module, ['upload_dir' => 'uploads/products/videos']);
            }
            if ($thumbnail && $thumbnail->isValid()) {
                $thumbnailPath = $this->storePublicUpload($thumbnail, $module, ['upload_dir' => 'uploads/products/video-thumbnails']);
            }

            $youtubeUrl = filled($row['youtube_url'] ?? null) ? trim((string) $row['youtube_url']) : null;
            if (($sourceType === 'upload' && blank($filePath)) || ($sourceType === 'youtube' && blank($youtubeUrl))) {
                continue;
            }

            $media->fill([
                'source_type' => $sourceType,
                'file_path' => $sourceType === 'upload' ? $filePath : null,
                'youtube_url' => $sourceType === 'youtube' ? $youtubeUrl : null,
                'title' => filled($row['title'] ?? null) ? trim((string) $row['title']) : null,
                'thumbnail_path' => $thumbnailPath,
                'language' => filled($row['language'] ?? null) ? trim((string) $row['language']) : null,
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ]);
            $media->save();
            $keptIds[] = $media->id;
        }

        $product->media()->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))->update(['is_active' => false]);
        if ($keptIds === []) {
            $product->media()->update(['is_active' => false]);
        }
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

    public function destroyImage(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        abort_unless((int) $image->product_id === (int) $product->getKey(), 404);

        $path = $image->path;
        $image->delete();
        $this->deletePublicUpload($path);

        return response()->json(['message' => 'Image deleted permanently.']);
    }

    public function destroyFieldImage(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'string'],
        ]);

        $field = $validated['field'];
        $allowedFields = collect($this->module()['fields'] ?? [])
            ->filter(fn (array $fieldConfig): bool => in_array($fieldConfig['type'] ?? '', ['file', 'image'], true))
            ->pluck('name')
            ->filter()
            ->reject(fn (string $name): bool => $name === 'primary_image')
            ->values()
            ->all();

        abort_unless(in_array($field, $allowedFields, true), 422, 'This image field cannot be deleted.');

        $path = $product->{$field};
        $product->forceFill([$field => null])->save();
        $this->deletePublicUpload($path);

        return response()->json(['message' => 'Image deleted permanently.']);
    }

    private function deletePublicUpload(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || Str::startsWith($path, ['http://', 'https://']) || str_contains($path, '..')) {
            return;
        }

        $absolutePath = public_path(ltrim(str_replace('\\', '/', $path), '/'));
        $publicRoot = realpath(public_path());
        $realFile = is_file($absolutePath) ? realpath($absolutePath) : false;

        if ($publicRoot && $realFile && Str::startsWith($realFile, $publicRoot)) {
            @unlink($realFile);
        }
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




