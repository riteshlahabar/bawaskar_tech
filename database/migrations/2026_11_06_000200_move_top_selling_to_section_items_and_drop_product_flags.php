<?php

use App\Models\Catalog\ProductHomepageSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Top Selling is picked in the content editor, and the Products form's whole
 * "Homepage / Display Flags" and "Deal Timer / Stock Display" blocks are gone.
 *
 * The data move matters more than the drop. On live, Top Selling row 21 has
 * ZERO products assigned to it - it renders purely from `is_top_selling`, which
 * 9 products carry - so dropping the flag without moving those 9 into content
 * rows would empty the row. The deal card (`is_deal_timer_product`) becomes row
 * 1 and hands its offer dates to the section.
 *
 * The 9 products keep their own `homepage_section_id` (all 4 = Pesticides on
 * live): a Top Selling product is no longer PLACED on the row, the row points
 * at it, so the two are independent.
 *
 * Deliberately NOT dropped: `show_on_homepage` (every homepage product query
 * filters on it), `homepage_sort_order` (orders a product inside a product
 * row) and the `homepage_*` banner columns (item 47 left them in place).
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private const FLAG_COLUMNS = [
        'is_featured',
        'is_top_selling',
        'is_trending',
        'is_new_arrival',
        'is_offer_product',
        'is_deal_timer_product',
    ];

    /** @var array<int, string> */
    private const DEAL_COLUMNS = [
        'sale_badge_text',
        'sold_quantity',
        'total_quantity',
        'low_stock_text',
        'is_offer_active',
        'offer_start_at',
        'offer_end_at',
    ];

    public function up(): void
    {
        $this->moveTopSellingProductsIntoContentRows();
        $this->unplaceProductsFromRowProductSections();

        Schema::table('products', function (Blueprint $table): void {
            foreach ([...self::FLAG_COLUMNS, ...self::DEAL_COLUMNS] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            foreach (self::FLAG_COLUMNS as $column) {
                if (! Schema::hasColumn('products', $column)) {
                    $table->boolean($column)->default(false);
                }
            }

            if (! Schema::hasColumn('products', 'sale_badge_text')) {
                $table->string('sale_badge_text', 80)->nullable();
            }

            if (! Schema::hasColumn('products', 'sold_quantity')) {
                $table->integer('sold_quantity')->nullable();
            }

            if (! Schema::hasColumn('products', 'total_quantity')) {
                $table->integer('total_quantity')->nullable();
            }

            if (! Schema::hasColumn('products', 'low_stock_text')) {
                $table->string('low_stock_text')->nullable();
            }

            if (! Schema::hasColumn('products', 'is_offer_active')) {
                $table->boolean('is_offer_active')->default(false);
            }

            if (! Schema::hasColumn('products', 'offer_start_at')) {
                $table->dateTime('offer_start_at')->nullable();
            }

            if (! Schema::hasColumn('products', 'offer_end_at')) {
                $table->dateTime('offer_end_at')->nullable();
            }
        });

        // The columns come back empty - the flags themselves cannot be
        // recovered. What CAN be undone is the content rows this migration
        // created, so that re-running `up` rebuilds them from scratch.
        DB::table('product_homepage_section_items')
            ->whereIn('section_id', $this->rowProductSectionIds())
            ->delete();

        DB::table('product_homepage_sections')
            ->whereIn('id', $this->rowProductSectionIds())
            ->update(['deal_starts_at' => null, 'deal_ends_at' => null, 'deal_timer_active' => false]);
    }

    /**
     * One content row per top-selling product, the deal card first, capped at
     * what the layout renders. A row that already holds content is left alone,
     * so this can never duplicate rows on a re-run.
     */
    private function moveTopSellingProductsIntoContentRows(): void
    {
        if (! Schema::hasColumn('products', 'is_top_selling')) {
            return;
        }

        $limit = ProductHomepageSection::CONTENT_ROW_LIMITS['top_selling_section'] ?? 9;
        $hasDealFlag = Schema::hasColumn('products', 'is_deal_timer_product');

        $products = DB::table('products')
            ->where('is_top_selling', true)
            ->orderByDesc($hasDealFlag ? 'is_deal_timer_product' : 'id')
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get(array_values(array_filter([
                'id',
                $hasDealFlag ? 'is_deal_timer_product' : null,
                Schema::hasColumn('products', 'is_offer_active') ? 'is_offer_active' : null,
                Schema::hasColumn('products', 'offer_start_at') ? 'offer_start_at' : null,
                Schema::hasColumn('products', 'offer_end_at') ? 'offer_end_at' : null,
            ])));

        if ($products->isEmpty()) {
            return;
        }

        foreach ($this->rowProductSectionIds() as $sectionId) {
            $alreadyHasRows = DB::table('product_homepage_section_items')
                ->where('section_id', $sectionId)
                ->exists();

            if ($alreadyHasRows) {
                continue;
            }

            $position = 0;
            $now = now();

            foreach ($products as $product) {
                $position++;

                DB::table('product_homepage_section_items')->insert([
                    'section_id' => $sectionId,
                    'product_id' => $product->id,
                    'slot' => $position === 1 ? 'deal' : 'product-'.($position - 1),
                    'sort_order' => $position,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->carryDealTimerToSection($sectionId, $products->first());
        }
    }

    /** The deal card's own offer dates become the row's countdown. */
    private function carryDealTimerToSection(int $sectionId, ?object $dealProduct): void
    {
        if (! $dealProduct) {
            return;
        }

        DB::table('product_homepage_sections')->where('id', $sectionId)->update([
            'deal_starts_at' => $dealProduct->offer_start_at ?? null,
            'deal_ends_at' => $dealProduct->offer_end_at ?? null,
            'deal_timer_active' => (bool) ($dealProduct->is_offer_active ?? false),
        ]);
    }

    /**
     * A product can no longer be placed on a row that picks its own products,
     * so any stale placement is cleared - otherwise the row's View page would
     * refuse to delete it over products it does not actually render.
     */
    private function unplaceProductsFromRowProductSections(): void
    {
        $sectionIds = $this->rowProductSectionIds();

        if ($sectionIds === []) {
            return;
        }

        DB::table('products')
            ->whereIn('homepage_section_id', $sectionIds)
            ->update(['homepage_section_id' => null]);
    }

    /** @return array<int, int> */
    private function rowProductSectionIds(): array
    {
        return DB::table('product_homepage_sections')
            ->whereIn('section_type', ProductHomepageSection::ROW_PRODUCT_SECTION_TYPES)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
};
