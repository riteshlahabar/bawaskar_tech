<?php

namespace App\Services\Catalog\Homepage;

use App\Contracts\Catalog\Homepage\HomepageSectionContentContract;
use App\Contracts\Files\PublicUploadContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

final class HomepageSectionContentService implements HomepageSectionContentContract
{
    private const IMAGE_DIR = 'uploads/homepage';

    private const VIDEO_DIR = 'uploads/homepage/videos';

    public function __construct(
        private readonly HomepageSectionContentFields $fields,
        private readonly PublicUploadContract $uploads,
    ) {}

    public function rowsFor(ProductHomepageSection $section): Collection
    {
        // Only live rows are editable. The one thing that deactivates a row is
        // the banner migration retiring a seeded Fastkart demo row it replaced,
        // and those must not come back as editable slots.
        $rows = $section->items()->where('is_active', true)->get();
        $limit = $section->contentRowLimit();

        // A fixed-count section shows exactly its own slots, so a two-column
        // Offer Zone opens with both rows even when empty and never with more.
        if ($limit !== null) {
            $rows = $rows->take($limit)->values();
        }

        $target = $limit ?? max(1, $rows->count());

        while ($rows->count() < $target) {
            $rows->push(new ProductHomepageSectionItem(['section_id' => $section->getKey()]));
        }

        return $rows->values();
    }

    public function rules(ProductHomepageSection $section, array $rows, array $files): array
    {
        $rules = ['rows' => ['array']];
        $sectionFields = $this->fields->for($section);

        foreach (array_keys($rows) as $index) {
            $row = (array) ($rows[$index] ?? []);
            $rowFiles = (array) ($files[$index] ?? []);

            $rules["rows.$index.id"] = ['nullable', 'integer'];
            $rules["rows.$index._delete"] = ['nullable'];

            // A row nobody touched is dropped rather than rejected, so none of
            // its fields can carry a required rule.
            if (! $this->isFilled($section, $row, $rowFiles)) {
                continue;
            }

            foreach ($sectionFields as $field) {
                $key = "rows.$index.$field";

                $rules[$key] = match ($field) {
                    'image_path', 'logo_image_path' => $this->fileRules($row, $rowFiles, $field, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
                    'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
                    'button_text' => ['nullable', 'string', 'max:80'],
                    'title' => $section->section_type === 'blog_section' || $section->section_type === 'service_section' || $section->section_type === 'coupon_section'
                        ? ['required', 'string', 'max:255']
                        : ['nullable', 'string', 'max:255'],
                    'subtitle' => ['nullable', 'string', 'max:255'],
                    'coupon_code' => ['required', 'string', 'max:80'],
                    'discount_text', 'validity_text' => ['nullable', 'string', 'max:255'],
                    'icon_key' => ['required', 'string', Rule::in(array_keys(HomepageSectionContentFields::SERVICE_ICONS))],
                    'video_source' => ['required', 'string', Rule::in(array_keys(HomepageSectionContentFields::VIDEO_SOURCES))],
                    'video_url' => ($row['video_source'] ?? 'url') === 'url'
                        ? ['required', 'string', 'url', 'max:2048']
                        : ['nullable', 'string', 'max:2048'],
                    'video_file_path' => ($row['video_source'] ?? 'url') === 'upload'
                        ? $this->fileRules($row, $rowFiles, 'video_file_path', ['file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'])
                        : ['nullable'],
                    default => ['nullable', 'string', 'max:255'],
                };
            }
        }

        return $rules;
    }

    public function validationMessages(ProductHomepageSection $section, array $rows = []): array
    {
        $messages = [];

        foreach (array_keys($rows ?: []) as $index) {
            $row = 'Row '.($index + 1);
            $messages["rows.$index.image_path.required"] = $row.': upload a banner image.';
            $messages["rows.$index.logo_image_path.required"] = $row.': upload a logo image.';
            $messages["rows.$index.title.required"] = $row.': enter a title.';
            $messages["rows.$index.coupon_code.required"] = $row.': enter the coupon code.';
            $messages["rows.$index.icon_key.required"] = $row.': choose an icon.';
            $messages["rows.$index.icon_key.in"] = $row.': choose an icon from the list.';
            $messages["rows.$index.video_source.required"] = $row.': choose a video source.';
            $messages["rows.$index.video_url.required"] = $row.': paste the video link.';
            $messages["rows.$index.video_url.url"] = $row.': the video link must be a full web address.';
            $messages["rows.$index.video_file_path.required"] = $row.': choose a video file to upload.';
            $messages["rows.$index.video_file_path.mimetypes"] = $row.': the video must be an MP4, WebM or OGG file.';
            $messages["rows.$index.product_id.exists"] = $row.': that product no longer exists.';
        }

        if ($section->usesContentRowProducts()) {
            $messages['deal.starts_at.date'] = 'Enter a valid Offer Start Date & Time.';
            $messages['deal.ends_at.date'] = 'Enter a valid Offer End Date & Time.';
            $messages['deal.ends_at.after_or_equal'] = 'The Offer End Date & Time must be on or after the start date.';
        }

        return $messages;
    }

    public function save(ProductHomepageSection $section, array $rows, array $files): array
    {
        $columns = $this->fields->columns($section);
        $existing = $section->items()->get()->keyBy('id');
        $saved = 0;
        $deleted = 0;
        $position = 0;

        foreach ($rows as $index => $row) {
            $row = (array) $row;
            $rowFiles = (array) ($files[$index] ?? []);
            $item = $this->existingRow($existing, $row);

            if (! empty($row['_delete'])) {
                if ($item) {
                    $this->forgetFiles($item);
                    $item->delete();
                    $deleted++;
                }

                continue;
            }

            if (! $item && ! $this->isFilled($section, $row, $rowFiles)) {
                continue;
            }

            $item ??= new ProductHomepageSectionItem(['section_id' => $section->getKey()]);
            $position++;

            foreach ($columns as $column) {
                $this->applyColumn($item, $column, $row, $rowFiles);
            }

            $item->slot = $this->slotFor($section, $position) ?? $item->slot;
            $item->sort_order = $position;
            $item->is_active = true;
            $item->section_id = $section->getKey();
            $item->save();
            $saved++;
        }

        return ['saved' => $saved, 'deleted' => $deleted];
    }

    public function productOptions(): array
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku'])
            ->mapWithKeys(fn (Product $product): array => [
                $product->id => trim($product->name.' ('.$product->sku.')'),
            ])
            ->all();
    }

    public function timerRules(ProductHomepageSection $section): array
    {
        if (! $section->usesContentRowProducts()) {
            return [];
        }

        return [
            'deal' => ['array'],
            'deal.starts_at' => ['nullable', 'date'],
            'deal.ends_at' => ['nullable', 'date', 'after_or_equal:deal.starts_at'],
            'deal.timer_active' => ['nullable', 'boolean'],
        ];
    }

    public function saveTimer(ProductHomepageSection $section, array $timer): void
    {
        if (! $section->usesContentRowProducts()) {
            return;
        }

        $section->deal_starts_at = filled($timer['starts_at'] ?? null) ? $timer['starts_at'] : null;
        $section->deal_ends_at = filled($timer['ends_at'] ?? null) ? $timer['ends_at'] : null;
        // An unticked switch posts nothing, so absence means off.
        $section->deal_timer_active = (bool) ($timer['timer_active'] ?? false);
        $section->save();
    }

    /**
     * Required only when the row has nothing stored and nothing uploaded, so
     * an existing image survives a save that does not replace it.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $rowFiles
     * @param  array<int, string>  $base
     * @return array<int, mixed>
     */
    private function fileRules(array $row, array $rowFiles, string $field, array $base): array
    {
        $hasStored = filled($row[$field.'_existing'] ?? null);
        $hasUpload = ($rowFiles[$field] ?? null) instanceof UploadedFile;

        return array_merge([$hasStored || $hasUpload ? 'nullable' : 'required'], $base);
    }

    /** @param  Collection<int, ProductHomepageSectionItem>  $existing */
    private function existingRow(Collection $existing, array $row): ?ProductHomepageSectionItem
    {
        $id = (int) ($row['id'] ?? 0);

        return $id > 0 ? $existing->get($id) : null;
    }

    /**
     * Has the admin put anything in this row? Keeps a padded blank slot from
     * being created as an empty item or failing validation.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $rowFiles
     */
    private function isFilled(ProductHomepageSection $section, array $row, array $rowFiles): bool
    {
        if ((int) ($row['id'] ?? 0) > 0) {
            return true;
        }

        foreach ($rowFiles as $file) {
            if ($file instanceof UploadedFile) {
                return true;
            }
        }

        foreach ($this->fields->for($section) as $field) {
            // The video source select always posts a value, so it can never
            // be the thing that makes a row count as filled.
            if ($field === 'video_source') {
                continue;
            }

            if (filled($row[$field] ?? null) || filled($row[$field.'_existing'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $row */
    private function applyColumn(ProductHomepageSectionItem $item, string $column, array $row, array $rowFiles): void
    {
        if (in_array($column, HomepageSectionContentFields::IMAGE_FIELDS, true)) {
            $this->applyUpload($item, $column, $rowFiles[$column] ?? null, self::IMAGE_DIR);

            return;
        }

        if ($column === 'video_file_path') {
            if (($row['video_source'] ?? 'url') === 'url') {
                // Switching a row to a link drops the file it used to serve.
                $this->uploads->delete($item->video_file_path);
                $item->video_file_path = null;

                return;
            }

            $this->applyUpload($item, $column, $rowFiles[$column] ?? null, self::VIDEO_DIR);

            return;
        }

        if ($column === 'video_url') {
            $item->video_url = ($row['video_source'] ?? 'url') === 'url' ? (trim((string) ($row['video_url'] ?? '')) ?: null) : null;

            return;
        }

        if ($column === 'product_id') {
            $item->product_id = filled($row['product_id'] ?? null) ? (int) $row['product_id'] : null;

            return;
        }

        $value = $row[$column] ?? null;
        $item->{$column} = filled($value) ? trim((string) $value) : null;
    }

    private function applyUpload(ProductHomepageSectionItem $item, string $column, mixed $file, string $directory): void
    {
        if ($file instanceof UploadedFile && $file->isValid()) {
            $this->uploads->delete($item->{$column});
            $item->{$column} = $this->uploads->store($file, $directory);
        }
    }

    /**
     * Fastkart renders these layouts by slot, so the slot follows row order
     * rather than being typed in: Offer Zone is left/right, Store Services is
     * service-1..5.
     */
    private function slotFor(ProductHomepageSection $section, int $position): ?string
    {
        return match ((string) $section->section_type) {
            'offer_section' => $position === 1 ? 'left' : 'right',
            'service_section' => 'service-'.$position,
            // Top Selling's first row is the Special Offer card beside the
            // product stack, so the slot records which one it is.
            'top_selling_section' => $position === 1 ? 'deal' : 'product-'.($position - 1),
            default => null,
        };
    }

    private function forgetFiles(ProductHomepageSectionItem $item): void
    {
        foreach (array_merge(HomepageSectionContentFields::IMAGE_FIELDS, ['video_file_path']) as $column) {
            $this->uploads->delete($item->{$column});
        }
    }
}
