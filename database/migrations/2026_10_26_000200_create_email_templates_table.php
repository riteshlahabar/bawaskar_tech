<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The wording of every automatic e-mail, editable in admin.
 *
 * The rows are inserted here rather than in a seeder, because the admin page
 * cannot create templates - a template only exists if code sends it - and a
 * missing row on live would leave the page empty with nothing to fix it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 60)->unique();
                $table->string('name', 120);
                $table->string('subject', 255);
                $table->text('body');
                // Off by default, like the master switch, so an upgrade is silent.
                $table->boolean('is_enabled')->default(false);
                $table->timestamps();
            });
        }

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
        Schema::dropIfExists('email_templates');
    }
};
