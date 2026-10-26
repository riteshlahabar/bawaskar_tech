<?php

namespace App\Services\Mail;

use App\Contracts\Mail\MailConfiguratorContract;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * SRP: copy one MailSetting row into config('mail'), nothing else.
 *
 * The already-built mailer instance is forgotten afterwards, otherwise Laravel
 * keeps using the transport it created from the old config earlier in the
 * request - which is exactly what makes a "Send Test Email" button lie.
 */
final class DatabaseMailConfigurator implements MailConfiguratorContract
{
    public function apply(MailSetting $setting): void
    {
        $mailer = $setting->mailer ?: 'smtp';

        Config::set('mail.default', $mailer);
        Config::set('mail.from.address', $setting->from_address);
        Config::set('mail.from.name', $setting->from_name ?: config('app.name'));

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.scheme', $setting->scheme());
            Config::set('mail.mailers.smtp.host', $setting->host);
            Config::set('mail.mailers.smtp.port', $setting->port ?: ($setting->encryption === 'ssl' ? 465 : 587));
            Config::set('mail.mailers.smtp.username', $setting->username);
            Config::set('mail.mailers.smtp.password', $setting->password);
            // A dead SMTP host must not hold the admin page open indefinitely.
            Config::set('mail.mailers.smtp.timeout', 15);
        }

        Mail::forgetMailers();
    }
}
