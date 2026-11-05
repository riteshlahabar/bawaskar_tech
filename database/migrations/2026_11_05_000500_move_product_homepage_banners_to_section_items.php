<?php

use App\Models\Catalog\ProductHomepageSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Moves the banners that were built on product rows into content items.
 *
 * Until now a banner could be built two ways and products silently won, so a
 * product assigned to a Hero Slider hid the banner uploaded under Add Content.
 * From item 47 a banner is a content row only. The images already uploaded
 * through the Products form's "Homepage Display Fields" are therefore copied
 * across, keeping the product link the user asked for ("with previous product
 * selection, because we already assign the product to it").
 *
 * Each moved product is then re-pointed at the product row that matches its
 * category rather than being left unplaced, so it stays visible in a product
 * section - `products.homepage_section_id` is a single column, which is why
 * the banner link had to move to the banner's own `product_id`.
 *
 * Live expectation (checked 2026-11-05): 7 items created - 1 on section 16,
 * 4 on 17, 2 on 22 - all 7 products moved onto the Pesticides row (category
 * 3), and Offer Zone's 2 seeded demo rows deactivated.
 */
return new class extends Migration
{
    /** Product column => content item column. */
    private const MAP = [
        'homepage_title' => 'title',
        'homepage_subtitle' => 'subtitle',
        'homepage_description' => 'description',
        'homepage_highlight_text' => 'highlight_text',
        'homepage_discount_text' => 'discount_text',
        'homepage_validity_text' => 'validity_text',
        'homepage_coupon_code' => 'coupon_code',
        'homepage_button_text' => 'button_text',
        'homepage_button_url' => 'button_url',
        'homepage_image_path' => 'image_path',
        'homepage_mobile_image_path' => 'mobile_image_path',
        'homepage_logo_image_path' => 'logo_image_path',
        'homepage_offer_image_path' => 'offer_image_path',
        'homepage_icon_key' => 'icon_key',
        'homepage_background_color' => 'background_color',
        'homepage_text_color' => 'text_color',
        'homepage_slot' => 'slot',
    ];

    public function up(): void
    {
        $sections = DB::table('product_homepage_sections')
            ->whereIn('section_type', ProductHomepageSection::CONTENT_SECTION_TYPES)
            ->pluck('section_type', 'id');

        if ($sections->isEmpty()) {
            return;
        }

        // Product rows keyed by the category they draw from, so a moved
        // product lands on the row that already shows its category.
        $rowsByCategory = DB::table('product_homepage_sections')
            ->where('section_type', 'product_section')
            ->whereNotNull('category_id')
            ->orderBy('sort_order')
            ->pluck('id', 'category_id');

        foreach ($sections as $sectionId => $sectionType) {
            $products = DB::table('products')
                ->where('homepage_section_id', $sectionId)
                ->orderBy('id')
                ->get();

            if ($products->isEmpty()) {
                continue;
            }

            $position = (int) DB::table('product_homepage_section_items')
                ->where('section_id', $sectionId)
                ->max('sort_order');

            $moved = 0;

            foreach ($products as $product) {
                $payload = $this->payload($product);

                // A product with no banner content was never a banner.
                if ($payload === null) {
                    continue;
                }

                $alreadyThere = DB::table('product_homepage_section_items')
                    ->where('section_id', $sectionId)
                    ->where('product_id', $product->id)
                    ->exists();

                if (! $alreadyThere) {
                    DB::table('product_homepage_section_items')->insert($payload + [
                        'section_id' => $sectionId,
                        'product_id' => $product->id,
                        'sort_order' => ++$position,
                        'is_active' => (int) $product->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $moved++;
                }

                DB::table('products')->where('id', $product->id)->update([
                    'homepage_section_id' => $rowsByCategory[$product->category_id] ?? null,
                    'updated_at' => now(),
                ]);
            }

            if ($moved > 0) {
                $this->deactivateSeededRows($sectionId);
                $this->renumber($sectionId, (string) $sectionType);
            }
        }
    }

    /**
     * Cannot be undone fully: the items it created are removed, but a product's
     * original `homepage_section_id` is not recoverable - it pointed at the
     * banner row that no longer owns it.
     */
    public function down(): void
    {
        DB::table('product_homepage_section_items')->whereNotNull('product_id')->delete();
    }

    /**
     * The row this product becomes, or null when it holds no banner content at
     * all (no image, no title, no highlight or discount text).
     *
     * @return array<string, mixed>|null
     */
    private function payload(object $product): ?array
    {
        $row = [];

        foreach (self::MAP as $from => $to) {
            $value = $product->{$from} ?? null;
            $row[$to] = filled($value) ? $value : null;
        }

        $hasContent = filled($row['image_path'])
            || filled($row['logo_image_path'])
            || filled($row['offer_image_path'])
            || filled($row['title'])
            || filled($row['highlight_text'])
            || filled($row['discount_text']);

        return $hasContent ? $row : null;
    }

    /**
     * Closes the gap the deactivated demo rows leave behind: the rows that
     * actually render are numbered 1..n, and the layouts that place content by
     * slot get theirs from that order (Offer Zone left/right, Store Services
     * service-1..5), exactly as the content editor assigns them on save.
     */
    private function renumber(int|string $sectionId, string $sectionType): void
    {
        $rows = DB::table('product_homepage_section_items')
            ->where('section_id', $sectionId)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id');

        foreach ($rows as $position => $id) {
            $update = ['sort_order' => $position + 1, 'updated_at' => now()];

            if ($sectionType === 'offer_section') {
                $update['slot'] = $position === 0 ? 'left' : 'right';
            } elseif ($sectionType === 'service_section') {
                $update['slot'] = 'service-'.($position + 1);
            }

            DB::table('product_homepage_section_items')->where('id', $id)->update($update);
        }
    }

    /**
     * A migrated banner replaces the seeded Fastkart demo rows on its section.
     * Offer Zone is a 2-slot layout already holding 2 such rows, invisible
     * today because its products win; appending 2 banners would leave 4 rows
     * for 2 slots and could resurface the demo images. Deactivated, never
     * deleted, so it stays reversible by hand.
     */
    private function deactivateSeededRows(int|string $sectionId): void
    {
        DB::table('product_homepage_section_items')
            ->where('section_id', $sectionId)
            ->whereNull('product_id')
            ->where('image_path', 'like', 'uploads/storefront/%')
            ->update(['is_active' => 0, 'updated_at' => now()]);
    }
};
