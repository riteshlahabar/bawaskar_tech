<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two more social links for the storefront footer, entered in
 * Settings -> Company Profile -> Google & Social Links.
 *
 * Both are text like the four that already exist, and both stay null, so the
 * footer shows no new icon until someone fills them in.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $columns = [
        'twitter_url',
        'threads_url',
    ];

    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            foreach ($this->columns as $column) {
                if (! Schema::hasColumn('company_settings', $column)) {
                    $table->text($column)->nullable()->after('youtube_url');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $drop = array_values(array_filter(
                $this->columns,
                static fn (string $column): bool => Schema::hasColumn('company_settings', $column)
            ));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
