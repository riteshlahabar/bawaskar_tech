<?php

namespace App\Observers\Mail;

use App\Contracts\Mail\EmailTemplateContract;
use App\Contracts\Mail\MailSenderContract;
use App\Mail\WelcomeMail;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

/**
 * Welcomes a new customer or dealer.
 *
 * Hung on User::created rather than on each registration controller, because
 * an account can be created from the customer app, the dealer app, the
 * storefront or admin, and all four should greet the person the same way.
 * Salesmen and admins are staff and get nothing.
 */
final class WelcomeMailObserver implements ShouldHandleEventsAfterCommit
{
    /** @var list<string> */
    private const BUYERS = [User::ROLE_CUSTOMER, User::ROLE_DEALER];

    public function __construct(
        private readonly MailSenderContract $mail,
        private readonly EmailTemplateContract $templates,
    ) {}

    public function created(User $user): void
    {
        try {
            if (! in_array((string) $user->role, self::BUYERS, true)) {
                return;
            }

            if (! $this->templates->enabled(EmailTemplate::WELCOME)) {
                return;
            }

            $template = $this->templates->render(EmailTemplate::WELCOME, [
                'name' => $user->name,
                'role' => ucfirst((string) $user->role),
            ]);

            $this->mail->send(new WelcomeMail($template), $user->email);
        } catch (Throwable) {
            // Never let a greeting stop an account from being created.
        }
    }
}
