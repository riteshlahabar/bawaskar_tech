<?php

namespace App\Observers\Sales;

use App\Contracts\Sales\InvoiceMailerContract;
use App\Models\Sales\Invoice;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

/**
 * Mails the invoice the moment it is raised - from admin, from the proforma
 * conversion or from the API, since all three end in the same created event.
 *
 * Whether it actually goes out is the mailer's decision (master switch plus
 * the Sale Invoice template's own switch), so this class only has to make the
 * call safely: after the transaction commits, so a rolled-back invoice never
 * mails, and swallowing every failure, so a dead SMTP server can never stop
 * an invoice from being saved.
 */
final class InvoiceMailObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly InvoiceMailerContract $mailer) {}

    public function created(Invoice $invoice): void
    {
        try {
            $this->mailer->send($invoice);
        } catch (Throwable) {
            // Already logged by the mailer; the admin Email Invoice button is the retry.
        }
    }
}
