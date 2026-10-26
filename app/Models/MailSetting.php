<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The single row behind Settings -> Mail Settings.
 *
 * Nothing here talks to the mailer; applying these values to config('mail') is
 * MailConfiguratorContract's job, and deciding whether to send is
 * MailSenderContract's. This model only stores and validates the facts.
 */
class MailSetting extends Model
{
    /** Transports worth offering on shared hosting. Anything else needs .env. */
    public const MAILERS = [
        'smtp' => 'SMTP (recommended)',
        'sendmail' => 'Sendmail (server program)',
        'log' => 'Log only — writes to laravel.log, sends nothing',
    ];

    /**
     * Stored as the words people see in cPanel. Laravel 13 wants an SMTP
     * `scheme` instead, so the mapping lives in scheme().
     */
    public const ENCRYPTIONS = [
        'tls' => 'TLS / STARTTLS (usually port 587)',
        'ssl' => 'SSL (usually port 465)',
        'none' => 'None (not recommended)',
    ];

    protected $fillable = [
        'is_enabled', 'mailer', 'host', 'port', 'username', 'password',
        'encryption', 'from_address', 'from_name', 'reply_to_address',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'port' => 'integer',
            // Never readable in the database, and never shown back in the form.
            'password' => 'encrypted',
        ];
    }

    /** The saved row, or an unsaved blank one so the form always has an object. */
    public static function current(): self
    {
        return static::query()->first() ?: new static;
    }

    /**
     * Enough filled in to attempt a send. `log` and `sendmail` need no host,
     * so only SMTP is checked for one.
     */
    public function isConfigured(): bool
    {
        if (blank($this->from_address)) {
            return false;
        }

        return $this->mailer !== 'smtp' || filled($this->host);
    }

    public function canSend(): bool
    {
        return $this->is_enabled && $this->isConfigured();
    }

    /** Laravel 13 SMTP scheme: smtps is implicit TLS, smtp is plain/STARTTLS. */
    public function scheme(): string
    {
        return $this->encryption === 'ssl' ? 'smtps' : 'smtp';
    }
}
