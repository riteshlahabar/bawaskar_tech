<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The salesman's stock answer on a dealer order they are still reviewing.
 *
 * This is deliberately separate from `orders.status`: "not available now" and
 * "available on <date>" are notes about stock, not steps of the order flow —
 * the order stays in `salesman_review` and can still be approved or cancelled
 * afterwards, so nothing here belongs in OrderStatusService::FLOW.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'availability')) {
                $table->string('availability', 30)->nullable()->after('status');
            }

            if (! Schema::hasColumn('orders', 'available_on')) {
                $table->timestamp('available_on')->nullable()->after('availability');
            }

            if (! Schema::hasColumn('orders', 'availability_note')) {
                $table->string('availability_note')->nullable()->after('available_on');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['availability', 'available_on', 'availability_note']);
        });
    }
};
