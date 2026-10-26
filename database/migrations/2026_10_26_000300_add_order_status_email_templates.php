<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the four order-status e-mails (packing, dispatched, out for delivery,
 * delivered) to the templates table.
 *
 * It inserts every key in EmailTemplate::DEFAULTS that has no row yet, so the
 * next template added to that list needs only a copy of this migration with a
 * new timestamp - and re-running it can never duplicate a row.
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
        DB::table('email_templates')
            ->whereIn('key', array_values(EmailTemplate::ORDER_STATUS_TEMPLATES))
            ->delete();
    }
};
