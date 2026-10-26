<?php

namespace App\Services\Mail;

use App\Contracts\Mail\MailConfiguratorContract;
use App\Contracts\Mail\MailSenderContract;
use App\Models\MailSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * SRP: apply the saved settings, then hand the mailable to Laravel.
 *
 * Sends are not queued. The queue worker on cPanel is a cron job that may or
 * may not be running, and a silently queued mail nobody ever delivers is worse
 * than a slow page.
 */
final class MailSender implements MailSenderContract
{
    public function __construct(private readonly MailConfiguratorContract $configurator) {}

    public function send(Mailable $mailable, ?string $to): bool
    {
        $to = trim((string) $to);

        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $setting = MailSetting::current();

        if (! $setting->canSend()) {
            return false;
        }

        try {
            $this->deliver($setting, $mailable, $to);

            return true;
        } catch (Throwable $failure) {
            Log::warning('Email not sent to '.$to.': '.$failure->getMessage());

            return false;
        }
    }

    public function sendOrFail(Mailable $mailable, string $to): void
    {
        $this->deliver(MailSetting::current(), $mailable, $to);
    }

    private function deliver(MailSetting $setting, Mailable $mailable, string $to): void
    {
        $this->configurator->apply($setting);

        if (filled($setting->reply_to_address)) {
            $mailable->replyTo($setting->reply_to_address);
        }

        Mail::to($to)->send($mailable);
    }
}
