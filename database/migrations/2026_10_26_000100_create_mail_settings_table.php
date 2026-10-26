<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SMTP credentials typed in admin instead of in .env, because the people who
 * run this site cannot edit .env on cPanel. One row only, like company_settings.
 *
 * The password column is text because the model casts it to `encrypted`, which
 * makes the stored value much longer than the password itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mail_settings')) {
            return;
        }

        Schema::create('mail_settings', function (Blueprint $table): void {
            $table->id();
            // Off by default, so an upgrade never starts sending mail on its own.
            $table->boolean('is_enabled')->default(false);
            $table->string('mailer', 20)->default('smtp');
            $table->string('host', 191)->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('username', 191)->nullable();
            $table->text('password')->nullable();
            $table->string('encryption', 10)->default('tls');
            $table->string('from_address', 191)->nullable();
            $table->string('from_name', 191)->nullable();
            $table->string('reply_to_address', 191)->nullable();
            // Separate switch: mail can be on while the automatic invoice mail is off.
            $table->boolean('invoice_email_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
