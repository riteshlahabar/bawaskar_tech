<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When "Send to Dispatch" creates the row at status Packing, it stamps this
 * so the record shows when packing actually started.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dispatches', 'packing_at')) {
            return;
        }

        Schema::table('dispatches', function (Blueprint $table): void {
            $table->timestamp('packing_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('dispatches', 'packing_at')) {
            return;
        }

        Schema::table('dispatches', function (Blueprint $table): void {
            $table->dropColumn('packing_at');
        });
    }
};
