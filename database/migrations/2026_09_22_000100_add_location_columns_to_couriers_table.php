<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same State / District / Taluka cascade already stored on `users` for
 * dealers, customers and salesmen (see 2026_09_17_000300). `pincode` already
 * exists on `couriers`; the old free-text `city` column is left in place but
 * unused from now on, replaced by `city_village`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('couriers', function (Blueprint $table): void {
            $table->unsignedSmallInteger('state_code')->nullable()->index();
            $table->string('state_name', 100)->nullable();
            $table->unsignedInteger('district_code')->nullable()->index();
            $table->string('district_name', 150)->nullable();
            $table->string('subdistrict_code', 20)->nullable();
            $table->string('subdistrict_name', 150)->nullable();
            $table->string('city_village', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table): void {
            $table->dropIndex(['state_code']);
            $table->dropIndex(['district_code']);
            $table->dropColumn([
                'state_code', 'state_name', 'district_code', 'district_name',
                'subdistrict_code', 'subdistrict_name', 'city_village',
            ]);
        });
    }
};
