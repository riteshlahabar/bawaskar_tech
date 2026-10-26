<?php

namespace App\Mail;

/**
 * Sent once, when a customer or dealer account is created - from either app
 * or from the storefront. Carries no data block of its own; the whole message
 * is the template's wording.
 */
class WelcomeMail extends TemplatedMail
{
    protected function templateView(): string
    {
        return 'emails.welcome';
    }
}
