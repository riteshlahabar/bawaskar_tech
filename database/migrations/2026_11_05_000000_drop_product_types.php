<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product type is removed from the catalog entirely.
 *
 * Two columns carried it: `products.product_type_id` (the Product Types admin
 * master) and the older `products.product_type` slug, which ProductInputService
 * kept mirrored from the chosen type and which the storefront navigation read.
 * Both are gone, and so is the `product_types` table.
 *
 * Every guard is explicit because on a fresh install the create migrations run
 * first and on live the columns already hold data; either way this must be a
 * no-op the second time it is seen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'product_type_id')) {
                $table->dropConstrainedForeignId('product_type_id');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'product_type')) {
                // MySQL drops the single-column index with the column.
                $table->dropColumn('product_type');
            }
        });

        Schema::dropIfExists('product_types');
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_types')) {
            Schema::create('product_types', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type', 40)->default('medicine')->index();
            }

            if (! Schema::hasColumn('products', 'product_type_id')) {
                $table->foreignId('product_type_id')->nullable()->after('brand_id')
                    ->constrained('product_types')->nullOnDelete();
            }
        });
    }
};
