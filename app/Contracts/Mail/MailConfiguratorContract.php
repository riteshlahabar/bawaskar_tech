<?php

namespace App\Contracts\Mail;

use App\Models\MailSetting;

/**
 * Puts the admin-entered mail settings into the runtime config.
 *
 * Applied just before a send rather than in a service provider, so a normal
 * page view never queries the table and a missing table never breaks boot.
 */
interface MailConfiguratorContract
{
    public function apply(MailSetting $setting): void;
}
