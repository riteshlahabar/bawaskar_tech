<?php

namespace App\Contracts\Catalog\Homepage;

use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use Illuminate\Support\Collection;

/**
 * The content rows of one homepage section: what the editor shows, what it
 * accepts, and how a save is applied.
 *
 * A section owns its content, which is why this is addressed by section and
 * not by item id. It replaces creating rows through the old generic 23-field
 * Homepage Setting Items form.
 */
interface HomepageSectionContentContract
{
    /**
     * Existing rows for the editor, padded with blanks up to the section's
     * fixed row count (a 2-slot Offer Zone always shows 2 rows). An unlimited
     * section is returned as-is, with one blank row when it is empty.
     *
     * @return Collection<int, ProductHomepageSectionItem>
     */
    public function rowsFor(ProductHomepageSection $section): Collection;

    /**
     * Validation rules for a posted `rows` array, built from the section
     * type's field set.
     *
     * The posted rows and files are needed because a required rule can only
     * apply to a row the admin actually filled in: a fixed-count section is
     * padded with blank rows, and a blank row is skipped, not rejected.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $files
     * @return array<string, mixed>
     */
    public function rules(ProductHomepageSection $section, array $rows, array $files): array;

    /**
     * Messages that name the row, so "Row 2: choose a product" beats
     * "rows.1.product_id is invalid".
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, string>
     */
    public function validationMessages(ProductHomepageSection $section, array $rows = []): array;

    /**
     * Applies the posted rows: updates existing items, creates new ones,
     * deletes rows the admin cleared, and stores any uploaded file.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $files  Per-row uploaded files.
     * @return array{saved: int, deleted: int}
     */
    public function save(ProductHomepageSection $section, array $rows, array $files): array;

    /**
     * Products for the row dropdown: id => "name (SKU)", alphabetical.
     *
     * @return array<int, string>
     */
    public function productOptions(): array;
}
