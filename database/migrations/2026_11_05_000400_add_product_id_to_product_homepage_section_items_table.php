<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A homepage banner points AT a product instead of being built from one.
 *
 * This is the column that lets the banner own the link: the content row holds
 * the image and the button text, and `product_id` says where the button goes.
 *
 * `nullOnDelete`, never cascade - deleting a product must break the link, not
 * delete the banner. Guarded with `hasColumn` and written as a separate
 * `Schema::table`: the original create migration is wrapped in
 * `if (! Schema::hasTable(...))` and can never run again on live, which is the
 * trap that left this table without `discount_text` until item 39.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('product_homepage_section_items', 'product_id')) {
            return;
        }

        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->after('section_id')
                ->constrained('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_homepage_section_items', 'product_id')) {
            return;
        }

        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
