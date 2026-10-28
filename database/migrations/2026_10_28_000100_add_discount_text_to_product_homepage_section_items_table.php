<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `product_homepage_section_items` is created inside a `Schema::hasTable()`
 * guard, so a database where that table already existed never received the
 * `discount_text` column and no later migration added it. Every save from the
 * Homepage Setting Items form then died with
 * "Unknown column 'discount_text' in 'INSERT INTO'", which is what took the
 * Video Section content form down on live.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('product_homepage_section_items', 'discount_text')) {
            return;
        }

        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            $table->string('discount_text')->nullable()->after('highlight_text');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_homepage_section_items', 'discount_text')) {
            return;
        }

        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            $table->dropColumn('discount_text');
        });
    }
};
