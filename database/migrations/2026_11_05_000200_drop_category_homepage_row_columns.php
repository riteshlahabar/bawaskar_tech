<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The "Homepage Product Row Settings" block is removed from Categories.
 *
 * Three of these five columns (`homepage_title`, `homepage_layout`,
 * `homepage_product_limit`) were read by nothing at all — no storefront view,
 * no API, no app. The other two only ordered the app's category list, which
 * now orders by name like every other category list.
 *
 * `categories.sort_order` and `categories.slug` are deliberately NOT touched:
 * slug builds the storefront category URL, and sort_order is still written by
 * the seeder and the product import.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private const COLUMNS = [
        'show_on_homepage',
        'homepage_title',
        'homepage_layout',
        'homepage_product_limit',
        'homepage_sort_order',
    ];

    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(false);
            }

            if (! Schema::hasColumn('categories', 'homepage_title')) {
                $table->string('homepage_title')->nullable();
            }

            if (! Schema::hasColumn('categories', 'homepage_layout')) {
                $table->string('homepage_layout', 80)->nullable();
            }

            if (! Schema::hasColumn('categories', 'homepage_product_limit')) {
                $table->integer('homepage_product_limit')->default(8);
            }

            if (! Schema::hasColumn('categories', 'homepage_sort_order')) {
                $table->integer('homepage_sort_order')->default(0);
            }
        });
    }
};
