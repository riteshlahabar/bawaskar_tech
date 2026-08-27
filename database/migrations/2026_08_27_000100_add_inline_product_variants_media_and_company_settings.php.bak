<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->longText('benefits')->nullable()->after('description');
            $table->longText('usage_instructions')->nullable()->after('benefits');
            $table->longText('crop_information')->nullable()->after('usage_instructions');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->decimal('size_value', 12, 3)->nullable()->after('value');
            $table->string('size_unit', 20)->nullable()->after('size_value');
            $table->string('variant_sku', 100)->nullable()->after('size_unit');
            $table->decimal('units_per_case', 12, 3)->default(1)->after('variant_sku');
            $table->decimal('mrp', 12, 2)->nullable()->after('units_per_case');
            $table->decimal('dealer_price', 12, 2)->nullable()->after('mrp');
            $table->decimal('customer_price', 12, 2)->nullable()->after('dealer_price');
        });

        DB::table('product_variants')->orderBy('id')->chunkById(100, function ($variants): void {
            $productPrices = DB::table('products')
                ->whereIn('id', $variants->pluck('product_id')->all())
                ->get(['id', 'mrp', 'dealer_price', 'customer_price'])
                ->keyBy('id');

            foreach ($variants as $variant) {
                $product = $productPrices->get($variant->product_id);
                preg_match('/^([0-9.]+)\s*([A-Za-z]+)?/', (string) $variant->value, $size);

                DB::table('product_variants')->where('id', $variant->id)->update([
                    'size_value' => $size[1] ?? null,
                    'size_unit' => isset($size[2]) ? strtoupper($size[2]) : null,
                    'units_per_case' => 1,
                    'mrp' => $product?->mrp,
                    'dealer_price' => $product?->dealer_price,
                    'customer_price' => $product?->customer_price,
                ]);
            }
        });

        Schema::table('inventory_batches', function (Blueprint $table): void {
            $table->dropUnique('inventory_batches_warehouse_id_product_id_batch_no_unique');
            $table->foreignId('product_variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
            $table->index(['product_id', 'product_variant_id']);
            $table->unique(['warehouse_id', 'product_id', 'product_variant_id', 'batch_no'], 'inventory_batch_variant_unique');
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->nullable()->after('inventory_batch_id')
                ->constrained('product_variants')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
            $table->string('variant_name')->nullable()->after('product_variant_id');
            $table->decimal('pack_quantity', 14, 3)->nullable()->after('quantity');
            $table->decimal('units_per_case', 12, 3)->default(1)->after('pack_quantity');
        });

        Schema::create('product_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('source_type', 20)->default('upload');
            $table->string('file_path')->nullable();
            $table->text('youtube_url')->nullable();
            $table->string('title')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('language', 10)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name');
            $table->string('logo_path')->nullable();
            $table->text('short_intro')->nullable();
            $table->longText('description')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('gst_number', 50)->nullable();
            $table->string('cin_number', 50)->nullable();
            $table->string('founder_name')->nullable();
            $table->string('chairman_name')->nullable();
            $table->string('managing_director_name')->nullable();
            $table->text('google_business_url')->nullable();
            $table->text('facebook_url')->nullable();
            $table->text('instagram_url')->nullable();
            $table->text('youtube_url')->nullable();
            $table->timestamps();
        });

        $legacySeller = DB::table('products')->whereNotNull('seller_name')->where('seller_name', '<>', '')->orderBy('id')->first();
        if ($legacySeller) {
            DB::table('company_settings')->insert([
                'company_name' => $legacySeller->seller_name,
                'logo_path' => $legacySeller->seller_logo,
                'short_intro' => $legacySeller->seller_description,
                'description' => $legacySeller->manufacturer_description,
                'address' => $legacySeller->seller_address,
                'phone' => $legacySeller->seller_contact,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('product_media');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn(['variant_name', 'pack_quantity', 'units_per_case']);
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_variant_id');
        });

        Schema::table('inventory_batches', function (Blueprint $table): void {
            $table->dropUnique('inventory_batch_variant_unique');
            $table->dropIndex(['product_id', 'product_variant_id']);
            $table->dropConstrainedForeignId('product_variant_id');
            $table->unique(['warehouse_id', 'product_id', 'batch_no']);
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn([
                'size_value', 'size_unit', 'variant_sku', 'units_per_case',
                'mrp', 'dealer_price', 'customer_price',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['benefits', 'usage_instructions', 'crop_information']);
        });
    }
};
