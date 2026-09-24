<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the salesman can say about an asset they hold.
 *
 * `return_requested_at` is deliberately NOT a status: handing a laptop back
 * is something only the admin can confirm, so the request is stamped here and
 * `status` stays `issued` until the admin marks it `returned`. Reporting an
 * asset lost or damaged does move `status`, because the salesman is the one
 * who knows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salesman_assets', function (Blueprint $table): void {
            if (! Schema::hasColumn('salesman_assets', 'return_requested_at')) {
                $table->timestamp('return_requested_at')->nullable()->after('returned_on');
            }

            if (! Schema::hasColumn('salesman_assets', 'salesman_remarks')) {
                $table->text('salesman_remarks')->nullable()->after('condition');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salesman_assets', function (Blueprint $table): void {
            $table->dropColumn(['return_requested_at', 'salesman_remarks']);
        });
    }
};
