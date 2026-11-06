<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Special Offer countdown moves from the product onto the homepage row.
 *
 * It used to live on `products` (the Deal Timer tab) together with a
 * `is_deal_timer_product` flag that picked which product was the deal card.
 * Both are gone: a Top Selling row now chooses its own products in the content
 * editor, its FIRST content row IS the deal card, and the dates below are the
 * row's own - so one row can run an offer while another does not.
 *
 * Written as a separate guarded `Schema::table` rather than an edit to the
 * original create migration, which is wrapped in `if (! Schema::hasTable(...))`
 * and can never run again on live (the trap behind item 39).
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private const COLUMNS = ['deal_starts_at', 'deal_ends_at', 'deal_timer_active'];

    public function up(): void
    {
        Schema::table('product_homepage_sections', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_homepage_sections', 'deal_starts_at')) {
                $table->dateTime('deal_starts_at')->nullable()->after('end_at');
            }

            if (! Schema::hasColumn('product_homepage_sections', 'deal_ends_at')) {
                $table->dateTime('deal_ends_at')->nullable()->after('deal_starts_at');
            }

            if (! Schema::hasColumn('product_homepage_sections', 'deal_timer_active')) {
                $table->boolean('deal_timer_active')->default(false)->after('deal_ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_homepage_sections', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                if (Schema::hasColumn('product_homepage_sections', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
