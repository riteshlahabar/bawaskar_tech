<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bank/account details for the invoice footer, plus which of the three PDF
 * designs every sales document should use.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $textColumns = [
        'account_name',
        'bank_name',
        'bank_branch',
        'account_number',
        'ifsc_code',
        'upi_id',
    ];

    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            foreach ($this->textColumns as $column) {
                if (! Schema::hasColumn('company_settings', $column)) {
                    $table->string($column, 191)->nullable()->after('cin_number');
                }
            }

            if (! Schema::hasColumn('company_settings', 'invoice_template')) {
                // Defaults to the existing design, so an upgrade changes nothing
                // until someone picks another one in admin.
                $table->string('invoice_template', 40)->default('classic')->after('upi_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $drop = array_values(array_filter(
                [...$this->textColumns, 'invoice_template'],
                static fn (string $column): bool => Schema::hasColumn('company_settings', $column)
            ));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
