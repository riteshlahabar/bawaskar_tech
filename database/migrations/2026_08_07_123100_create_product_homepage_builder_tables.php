<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_homepage_sections')) {
            Schema::create('product_homepage_sections', function (Blueprint $table): void {
                $table->id();
                $table->string('section_key', 120)->unique();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('section_type', 60)->index();
                $table->string('layout_type', 80)->nullable()->index();
                $table->string('source_type', 60)->nullable()->index();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->unsignedInteger('product_limit')->default(8);
                $table->unsignedInteger('item_limit')->default(0);
                $table->string('image_size_note')->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->dateTime('start_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_homepage_section_items')) {
            Schema::create('product_homepage_section_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('section_id')->constrained('product_homepage_sections')->cascadeOnDelete();
                $table->string('slot', 60)->nullable()->index();
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable();
                $table->text('description')->nullable();
                $table->string('highlight_text')->nullable();
                $table->string('coupon_code', 80)->nullable();
                $table->string('validity_text')->nullable();
                $table->string('button_text')->nullable();
                $table->string('button_url')->nullable();
                $table->string('image_path')->nullable();
                $table->string('mobile_image_path')->nullable();
                $table->string('logo_image_path')->nullable();
                $table->string('icon_key')->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->decimal('old_price', 12, 2)->nullable();
                $table->unsignedInteger('sold_quantity')->nullable();
                $table->unsignedInteger('total_quantity')->nullable();
                $table->dateTime('timer_end_at')->nullable();
                $table->string('background_color', 30)->nullable();
                $table->string('text_color', 30)->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_homepage_section_products')) {
            Schema::create('product_homepage_section_products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('section_id')->constrained('product_homepage_sections')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->unique(['section_id', 'product_id']);
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            $columns = Schema::getColumnListing('products');
            $has = fn (string $column): bool => in_array($column, $columns, true);

            if (! $has('short_description')) $table->text('short_description')->nullable()->after('description');
            if (! $has('detail_banner_image')) $table->string('detail_banner_image')->nullable()->after('manufacturer_details');
            if (! $has('detail_banner_url')) $table->string('detail_banner_url')->nullable()->after('detail_banner_image');
            if (! $has('detail_sidebar_banner_image')) $table->string('detail_sidebar_banner_image')->nullable()->after('detail_banner_url');
            if (! $has('detail_sidebar_banner_url')) $table->string('detail_sidebar_banner_url')->nullable()->after('detail_sidebar_banner_image');
            if (! $has('seller_name')) $table->string('seller_name')->nullable()->after('detail_sidebar_banner_url');
            if (! $has('seller_logo')) $table->string('seller_logo')->nullable()->after('seller_name');
            if (! $has('seller_description')) $table->text('seller_description')->nullable()->after('seller_logo');
            if (! $has('seller_address')) $table->string('seller_address')->nullable()->after('seller_description');
            if (! $has('seller_contact')) $table->string('seller_contact')->nullable()->after('seller_address');
            if (! $has('manufacturer_title')) $table->string('manufacturer_title')->nullable()->after('seller_contact');
            if (! $has('manufacturer_description')) $table->text('manufacturer_description')->nullable()->after('manufacturer_title');
            if (! $has('sale_badge_text')) $table->string('sale_badge_text', 80)->nullable()->after('manufacturer_description');
            if (! $has('sold_quantity')) $table->unsignedInteger('sold_quantity')->nullable()->after('sale_badge_text');
            if (! $has('total_quantity')) $table->unsignedInteger('total_quantity')->nullable()->after('sold_quantity');
            if (! $has('low_stock_text')) $table->string('low_stock_text')->nullable()->after('total_quantity');
            if (! $has('is_top_selling')) $table->boolean('is_top_selling')->default(false)->after('is_featured');
            if (! $has('is_trending')) $table->boolean('is_trending')->default(false)->after('is_top_selling');
            if (! $has('is_new_arrival')) $table->boolean('is_new_arrival')->default(false)->after('is_trending');
            if (! $has('is_deal_timer_product')) $table->boolean('is_deal_timer_product')->default(false)->after('is_new_arrival');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_homepage_section_products');
        Schema::dropIfExists('product_homepage_section_items');
        Schema::dropIfExists('product_homepage_sections');

        Schema::table('products', function (Blueprint $table): void {
            foreach ([
                'short_description','detail_banner_image','detail_banner_url','detail_sidebar_banner_image','detail_sidebar_banner_url',
                'seller_name','seller_logo','seller_description','seller_address','seller_contact','manufacturer_title','manufacturer_description',
                'sale_badge_text','sold_quantity','total_quantity','low_stock_text','is_top_selling','is_trending','is_new_arrival','is_deal_timer_product',
            ] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};