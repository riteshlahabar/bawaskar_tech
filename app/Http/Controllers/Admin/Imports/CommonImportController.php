<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\Catalog\Unit;
use App\Models\Inventory\Warehouse;
use App\Models\Storefront\StorefrontSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ZipArchive;

class CommonImportController extends Controller
{
    private array $allowedModules = [
        'products',
        'categories',
        'brands',
        'units',
        'inventory',
        'warehouses',
        'batches',
        'storefront-banners',
        'storefront-sections',
        'storefront-section-products',
        'storefront-service-blocks',
        'storefront-footer-links',
    ];

    public function store(Request $request, string $module): RedirectResponse
    {
        abort_unless(in_array($module, $this->allowedModules, true), 404);

        $moduleConfig = config('admin.modules.'.$module);
        abort_unless($moduleConfig && ! empty($moduleConfig['model']), 404);

        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
            'images_zip' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $rows = $this->readRows(
            $request->file('import_file')->getRealPath(),
            $request->file('import_file')->getClientOriginalExtension()
        );

        if (count($rows) < 2) {
            return back()->with('error', 'Import file is empty or headers are missing.');
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), array_shift($rows));

        $this->extractImagesByExcelPaths($request, $headers, $rows, $module);
        $model = $moduleConfig['model'];
        $fields = collect($moduleConfig['fields'] ?? [])->pluck('name')->filter()->values()->all();

        $created = 0;
        $updated = 0;
        $failed = 0;
        $firstError = null;

        foreach ($rows as $index => $values) {
            $line = $index + 2;
            $values = array_pad($values, count($headers), null);
            $row = array_combine($headers, array_slice($values, 0, count($headers)));

            if (! $row || count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            try {
                if ($request->filled('placement') && $module === 'storefront-banners') {
                    $row['placement'] = $request->query('placement');
                }

                if ($request->filled('section_key') && in_array($module, ['storefront-sections', 'storefront-section-products'], true)) {
                    $row['section_key'] = $request->query('section_key');
                }

                $data = $this->buildData($row, $fields, $module);
                $data = $this->resolveRelations($data, $row, $module);
                $this->applyStorefrontBannerProductLink($data, $row, $module);

                $productImagePath = $this->firstFilled($row, ['primary_image', 'image_path', 'product_image']);
                unset($data['primary_image']);

                $where = $this->uniqueWhere($data, $module);

                if (empty($where)) {
                    $record = $model::query()->create($data);
                    $created++;
                } else {
                    $record = $model::query()->where($where)->first();

                    if ($record) {
                        $record->fill($data)->save();
                        $updated++;
                    } else {
                        $record = $model::query()->create($data);
                        $created++;
                    }
                }

                if ($module === 'products' && $record instanceof Product && $productImagePath) {
                    $this->syncProductImage($record, $productImagePath);
                }
            } catch (\Throwable $e) {
                $failed++;
                $firstError ??= 'Line '.$line.': '.$e->getMessage();
            }
        }

        $this->bumpCacheVersion($module);

        $message = "Import completed. Created: {$created}, Updated: {$updated}, Failed: {$failed}.";

        if ($failed > 0 && $firstError) {
            return back()->with('warning', $message.' First error: '.$firstError);
        }

        return back()->with('success', $message);
    }


    private function extractImagesByExcelPaths(Request $request, array $headers, array $rows, string $module): void
    {
        if (! $request->hasFile('images_zip')) {
            return;
        }

        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive extension is required for image ZIP upload.');
        }

        $zipPath = $request->file('images_zip')->getRealPath();
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open image ZIP file.');
        }

        $imageColumns = ['image_path', 'primary_image', 'product_image', 'icon_path'];
        $wantedImages = [];

        foreach ($rows as $values) {
            $values = array_pad($values, count($headers), null);
            $row = array_combine($headers, array_slice($values, 0, count($headers)));

            if (! $row) {
                continue;
            }

            foreach ($imageColumns as $column) {
                if (empty($row[$column])) {
                    continue;
                }

                $targetPath = $this->normalizeImportImagePath((string) $row[$column], $module);

                if ($targetPath) {
                    $wantedImages[basename($targetPath)] = $targetPath;
                }
            }
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = str_replace('\\', '/', $zip->getNameIndex($i));
            $entry = trim($entry, '/');

            if ($entry === '' || str_ends_with($entry, '/') || str_contains($entry, '..')) {
                continue;
            }

            $fileName = basename($entry);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (! in_array($extension, $allowedExtensions, true)) {
                continue;
            }

            if (! isset($wantedImages[$fileName])) {
                continue;
            }

            $targetRelativePath = $wantedImages[$fileName];
            $targetFullPath = public_path($targetRelativePath);
            $targetDir = dirname($targetFullPath);

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            copy('zip://'.$zipPath.'#'.$entry, $targetFullPath);
        }

        $zip->close();
    }

    private function normalizeImportImagePath(string $path, string $module): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || preg_match('/^https?:\/\//i', $path)) {
            return '';
        }

        if (str_starts_with($path, 'uploads/')) {
            return $path;
        }

        return match ($module) {
            'storefront-banners' => 'uploads/storefront/banners/home-import/'.basename($path),
            'products' => 'uploads/products/import/'.basename($path),
            default => 'uploads/imports/'.$module.'/'.basename($path),
        };
    }
    private function buildData(array $row, array $fields, string $module): array
    {
        $data = [];

        foreach ($fields as $field) {
            $key = $this->normalizeHeader($field);

            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);

            if ($value === '') {
                continue;
            }

            $data[$field] = $this->castValue($field, $value);
        }

        foreach (['image_path', 'primary_image', 'product_image', 'icon_path'] as $imageField) {
            if (! empty($data[$imageField])) {
                $data[$imageField] = $this->normalizeImportImagePath((string) $data[$imageField], $module);
            }
        }

        if (empty($data['slug']) && ! empty($data['name']) && $module === 'categories') {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }

    private function resolveRelations(array $data, array $row, string $module): array
    {
        $category = $this->findCategory($row);
        if ($category && ($module === 'products' || $module === 'storefront-sections')) {
            $data['category_id'] = $category->id;
        }

        $brand = $this->findByName(Brand::class, $this->firstFilled($row, ['brand_name', 'brand']));
        if ($brand && $module === 'products') {
            $data['brand_id'] = $brand->id;
        }

        $unit = $this->findUnit($row);
        if ($unit && $module === 'products') {
            $data['unit_id'] = $unit->id;
        }

        $warehouse = $this->findWarehouse($row);
        if ($warehouse && in_array($module, ['batches', 'inventory'], true)) {
            $data['warehouse_id'] = $warehouse->id;
        }

        $product = $this->findProduct($row);
        if ($product && in_array($module, ['batches', 'inventory', 'storefront-section-products'], true)) {
            $data['product_id'] = $product->id;
        }

        $section = $this->findSection($row);
        if ($section && $module === 'storefront-section-products') {
            $data['section_id'] = $section->id;
        }

        return $data;
    }

    private function applyStorefrontBannerProductLink(array &$data, array $row, string $module): void
    {
        if ($module !== 'storefront-banners') {
            return;
        }

        if (! empty($data['button_url'])) {
            return;
        }

        $product = $this->findProduct($row);

        if ($product) {
            $data['button_url'] = route('store.product', ['product' => $product->id], false);
        }
    }

    private function uniqueWhere(array $data, string $module): array
    {
        return match ($module) {
            'storefront-banners' => array_filter([
                'placement' => $data['placement'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),

            'storefront-sections' => ! empty($data['section_key']) ? ['section_key' => $data['section_key']] : [],

            'storefront-section-products' => array_filter([
                'section_id' => $data['section_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
            ]),

            'storefront-service-blocks' => array_filter([
                'title' => $data['title'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),

            'storefront-footer-links' => array_filter([
                'link_group' => $data['link_group'] ?? null,
                'title' => $data['title'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),

            'categories' => ! empty($data['slug']) ? ['slug' => $data['slug']] : (! empty($data['name']) ? ['name' => $data['name']] : []),
            'brands' => ! empty($data['name']) ? ['name' => $data['name']] : [],
            'units' => ! empty($data['short_name']) ? ['short_name' => $data['short_name']] : (! empty($data['name']) ? ['name' => $data['name']] : []),
            'products' => ! empty($data['sku']) ? ['sku' => $data['sku']] : (! empty($data['name']) ? ['name' => $data['name']] : []),
            'warehouses' => ! empty($data['code']) ? ['code' => $data['code']] : (! empty($data['name']) ? ['name' => $data['name']] : []),

            'batches', 'inventory' => array_filter([
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'batch_no' => $data['batch_no'] ?? null,
            ]),

            default => [],
        };
    }

    private function readRows(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'xlsx') {
            return $this->readXlsx($path);
        }

        $rows = [];
        $handle = fopen($path, 'r');

        if (! $handle) {
            return [];
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive extension is required for XLSX import. Save the Excel file as CSV and import again.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Unable to open XLSX file.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);

            foreach ($xml->si ?? [] as $si) {
                $text = '';

                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }

                $sharedStrings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('First worksheet not found in XLSX file.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row ?? [] as $rowNode) {
            $row = [];

            foreach ($rowNode->c as $cell) {
                $ref = (string) $cell['r'];
                $index = $this->excelColumnIndex($ref);
                $type = (string) $cell['t'];

                if ($type === 's') {
                    $value = $sharedStrings[(int) $cell->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $row[$index] = $value;
            }

            if ($row !== []) {
                ksort($row);
                $maxIndex = max(array_keys($row));
                $filledRow = [];

                for ($i = 0; $i <= $maxIndex; $i++) {
                    $filledRow[] = $row[$i] ?? '';
                }

                $rows[] = $filledRow;
            }
        }

        return $rows;
    }

    private function excelColumnIndex(string $cellReference): int
    {
        preg_match('/^[A-Z]+/', strtoupper($cellReference), $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function castValue(string $field, mixed $value): mixed
    {
        $value = trim((string) $value);
        $lower = strtolower($value);

        if (in_array($field, ['is_active', 'is_featured', 'is_visible_to_customers', 'is_visible_to_dealers'], true)) {
            return in_array($lower, ['1', 'yes', 'true', 'active', 'on'], true);
        }

        if (str_contains($field, 'date') && is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        if (str_contains($field, 'date')) {
            return Carbon::parse($value)->toDateString();
        }

        if (in_array($field, ['sort_order', 'decimal_precision', 'product_limit'], true)) {
            return (int) $value;
        }

        if (in_array($field, ['gst_percent', 'mrp', 'customer_price', 'dealer_price', 'purchase_price', 'quantity', 'reserved_quantity', 'low_stock_alert'], true)) {
            return (float) $value;
        }

        return $value;
    }

    private function findProduct(array $row): ?Product
    {
        $id = $this->firstFilled($row, ['product_id']);
        $sku = $this->firstFilled($row, ['product_sku', 'sku']);
        $name = $this->firstFilled($row, ['product_name', 'product']);

        return Product::query()
            ->when($id, fn ($query) => $query->where('id', $id))
            ->when(! $id && $sku, fn ($query) => $query->where('sku', $sku))
            ->when(! $id && ! $sku && $name, fn ($query) => $query->where('name', $name))
            ->first();
    }

    private function findCategory(array $row): ?Category
    {
        $id = $this->firstFilled($row, ['category_id']);
        $slug = $this->firstFilled($row, ['category_slug', 'slug']);
        $name = $this->firstFilled($row, ['category_name', 'category']);

        return Category::query()
            ->when($id, fn ($query) => $query->where('id', $id))
            ->when(! $id && $slug, fn ($query) => $query->where('slug', $slug))
            ->when(! $id && ! $slug && $name, fn ($query) => $query->where('name', $name))
            ->first();
    }

    private function findUnit(array $row): ?Unit
    {
        $id = $this->firstFilled($row, ['unit_id']);
        $shortName = $this->firstFilled($row, ['unit_short_name', 'short_name', 'unit']);
        $name = $this->firstFilled($row, ['unit_name']);

        return Unit::query()
            ->when($id, fn ($query) => $query->where('id', $id))
            ->when(! $id && $shortName, fn ($query) => $query->where('short_name', $shortName))
            ->when(! $id && ! $shortName && $name, fn ($query) => $query->where('name', $name))
            ->first();
    }

    private function findWarehouse(array $row): ?Warehouse
    {
        $id = $this->firstFilled($row, ['warehouse_id']);
        $code = $this->firstFilled($row, ['warehouse_code', 'code']);
        $name = $this->firstFilled($row, ['warehouse_name', 'warehouse']);

        return Warehouse::query()
            ->when($id, fn ($query) => $query->where('id', $id))
            ->when(! $id && $code, fn ($query) => $query->where('code', $code))
            ->when(! $id && ! $code && $name, fn ($query) => $query->where('name', $name))
            ->first();
    }

    private function findSection(array $row): ?StorefrontSection
    {
        $id = $this->firstFilled($row, ['section_id']);
        $key = $this->firstFilled($row, ['section_key']);
        $title = $this->firstFilled($row, ['section_title', 'section']);

        return StorefrontSection::query()
            ->when($id, fn ($query) => $query->where('id', $id))
            ->when(! $id && $key, fn ($query) => $query->where('section_key', $key))
            ->when(! $id && ! $key && $title, fn ($query) => $query->where('title', $title))
            ->first();
    }

    private function findByName(string $model, ?string $name): ?Model
    {
        if (! $name) {
            return null;
        }

        return $model::query()->where('name', $name)->first();
    }

    private function firstFilled(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $key = $this->normalizeHeader($key);

            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }

        return null;
    }

    private function syncProductImage(Product $product, string $path): void
    {
        ProductImage::query()->updateOrCreate(
            ['product_id' => $product->id, 'is_primary' => true],
            ['path' => trim($path, '/'), 'sort_order' => 1]
        );
    }

    private function bumpCacheVersion(string $module): void
    {
        if (in_array($module, ['products', 'categories', 'brands', 'units', 'inventory', 'batches'], true)) {
            Cache::forever('catalog_cache_version', ((int) Cache::get('catalog_cache_version', 1)) + 1);
        }
    }

    private function normalizeHeader(string $header): string
    {
        $header = Str::of($header)->lower()->trim()->toString();
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim((string) $header, '_');
    }

    public function sample(string $module)
    {
        abort_unless(in_array($module, $this->allowedModules, true), 404);

        $moduleConfig = config('admin.modules.'.$module);
        abort_unless($moduleConfig, 404);

        $sampleFile = public_path('excel/'.$module.'/'.$module.'_sample.csv');

        if (is_file($sampleFile)) {
            return response()->download($sampleFile, str_replace('-', '_', $module).'_sample.csv', [
                'Content-Type' => 'text/csv',
            ]);
        }

        $headers = $this->sampleHeaders($module, $moduleConfig);
        $row = $this->sampleRow($module, $headers);

        $fileName = str_replace('-', '_', $module).'_sample.csv';

        return response()->streamDownload(function () use ($headers, $row) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $row);
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function sampleHeaders(string $module, array $moduleConfig): array
    {
        $fields = collect($moduleConfig['fields'] ?? [])
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        $extra = match ($module) {
            'products' => ['category_name', 'brand_name', 'unit_short_name'],
            'categories' => ['parent_name'],
            'inventory', 'batches' => ['product_sku', 'warehouse_code'],
            'storefront-banners' => ['product_sku'],
            'storefront-sections' => ['category_name'],
            'storefront-section-products' => ['section_key', 'product_sku'],
            default => [],
        };

        return array_values(array_unique(array_merge($extra, $fields)));
    }

    private function sampleRow(string $module, array $headers): array
    {
        $examples = [
            'placement' => 'hero_main',
            'title' => 'Premium Pesticides & Fertilizers',
            'subtitle' => 'Farmer Special Offer',
            'description' => 'Better crop protection and growth',
            'button_text' => 'Shop Now',
            'button_url' => '',
            'product_sku' => 'PES001',
            'image_path' => 'uploads/storefront/banners/banner1.jpg',
            'sort_order' => '1',
            'is_active' => '1',

            'category_name' => 'Pesticides',
            'brand_name' => 'Bawasakar',
            'unit_short_name' => 'ltr',
            'sku' => 'PES001',
            'name' => 'Premium Pesticide',
            'product_type' => 'fertilizer',
            'hsn_code' => '3808',
            'gst_percent' => '18',
            'mrp' => '500',
            'dealer_price' => '420',
            'customer_price' => '480',
            'primary_image' => 'uploads/products/pesticide.jpg',
            'is_featured' => '1',
            'is_visible_to_dealers' => '1',
            'is_visible_to_customers' => '1',

            'short_name' => 'ltr',
            'unit_type' => 'volume',
            'decimal_precision' => '2',

            'warehouse_code' => 'WH001',
            'code' => 'WH001',
            'batch_no' => 'BATCH001',
            'quantity' => '100',
            'reserved_quantity' => '0',
            'low_stock_alert' => '10',
            'expiry_date' => date('Y-m-d', strtotime('+1 year')),

            'section_key' => 'featured_products',
            'section_type' => 'product',
            'source_type' => 'manual',
            'product_limit' => '10',
        ];

        return array_map(fn ($header) => $examples[$header] ?? '', $headers);
    }

}