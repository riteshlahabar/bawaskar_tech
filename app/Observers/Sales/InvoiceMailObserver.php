<?php

namespace App\Observers\Sales;

use App\Contracts\Sales\InvoiceMailerContract;
use App\Models\MailSetting;
use App\Models\Sales\Invoice;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

/**
 * Mails the invoice the moment it is raised - from admin, from the proforma
 * conversion or from the API, since all three end in the same created event.
 *
 * Runs after the transaction commits, so a rolled-back invoice never mails,
 * and swallows every failure, so a dead SMTP server can never stop an invoice
 * from being saved. The same rule the notification observers follow.
 */
final class InvoiceMailObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly InvoiceMailerContract $mailer) {}

    public function created(Invoice $invoice): void
    {
        try {
            if (MailSetting::current()->canSendInvoiceEmail()) {
                $this->mailer->send($invoice);
            }
        } catch (Throwable) {
            // Already logged by the mailer; the admin Email Invoice button is the retry.
        }
    }
}
