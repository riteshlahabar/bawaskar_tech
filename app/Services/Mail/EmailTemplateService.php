<?php

namespace App\Services\Mail;

use App\Contracts\Mail\EmailTemplateContract;
use App\Models\CompanySetting;
use App\Models\EmailTemplate;
use App\Models\MailSetting;
use App\Support\Mail\RenderedTemplate;
use Illuminate\Support\Facades\Cache;

/**
 * SRP: key + values in, finished subject and body out.
 *
 * The rows are read once per request and cached for a minute, because an
 * order that mails the buyer would otherwise hit the table on every save.
 * Saving a template clears the cache through EmailTemplate's boot hooks.
 */
final class EmailTemplateService implements EmailTemplateContract
{
    private const CACHE_KEY = 'email_templates.all';

    public function render(string $key, array $replace = []): RenderedTemplate
    {
        $row = $this->row($key);
        $default = EmailTemplate::DEFAULTS[$key] ?? ['subject' => '', 'body' => ''];

        // An emptied field falls back to the shipped wording rather than
        // sending a blank subject or an empty body.
        $subject = trim((string) ($row['subject'] ?? '')) ?: $default['subject'];
        $body = trim((string) ($row['body'] ?? '')) ?: $default['body'];

        $values = $this->values($replace);

        return new RenderedTemplate(
            strtr($subject, $values),
            strtr($body, $values),
            (bool) ($row['is_enabled'] ?? false),
        );
    }

    public function enabled(string $key): bool
    {
        return MailSetting::current()->canSend() && (bool) ($this->row($key)['is_enabled'] ?? false);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function row(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 60, static fn (): array => EmailTemplate::query()
            ->get(['key', 'subject', 'body', 'is_enabled'])
            ->keyBy('key')
            ->map(static fn (EmailTemplate $template): array => $template->only(['subject', 'body', 'is_enabled']))
            ->all());
    }

    /**
     * @param  array<string, scalar|null>  $replace
     * @return array<string, string>
     */
    private function values(array $replace): array
    {
        // :company is on every template, so it is filled in here rather than
        // by each caller.
        $replace += ['company' => CompanySetting::query()->value('company_name') ?: config('app.name')];

        $values = [];

        foreach ($replace as $name => $value) {
            $values[':'.ltrim((string) $name, ':')] = (string) $value;
        }

        return $values;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
