<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Order Cancelled e-mail.
 *
 * Same insert-every-missing-key loop as the migration before it, so it is
 * idempotent and also picks up any template added to EmailTemplate::DEFAULTS
 * since then.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (EmailTemplate::DEFAULTS as $key => $default) {
            if (DB::table('email_templates')->where('key', $key)->exists()) {
                continue;
            }

            DB::table('email_templates')->insert([
                'key' => $key,
                'name' => $default['name'],
                'subject' => $default['subject'],
                'body' => $default['body'],
                'is_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'order_cancelled')->delete();
    }
};
