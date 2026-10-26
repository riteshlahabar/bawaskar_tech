<?php

namespace App\Contracts\Sales;

use App\Models\Sales\Invoice;

/**
 * Mails a Sale Invoice, PDF attached, to the customer or dealer it belongs to.
 */
interface InvoiceMailerContract
{
    /** The buyer's e-mail address, or null when the account has none. */
    public function recipient(Invoice $invoice): ?string;

    /** Automatic path: never throws, returns false when nothing was sent. */
    public function send(Invoice $invoice): bool;

    /**
     * Admin "Email Invoice" button: the reason for a failure has to reach the
     * person who clicked, so this one throws.
     *
     * @throws \RuntimeException|\Throwable
     */
    public function sendOrFail(Invoice $invoice): void;
}
