<?php

namespace App\Contracts\Mail;

use Illuminate\Mail\Mailable;

/**
 * The only way the application sends e-mail.
 *
 * It owns the two decisions every caller would otherwise repeat: whether mail
 * is switched on at all, and what to do when the SMTP server refuses.
 */
interface MailSenderContract
{
    /**
     * Send and never throw — a mail problem must not fail the order, invoice
     * or page that triggered it. Returns false when mail is off, the address
     * is empty, or the send failed (the reason is logged).
     */
    public function send(Mailable $mailable, ?string $to): bool;

    /**
     * Same send, but lets the failure through. Only for the admin "Send Test
     * Email" button, where the whole point is to show the real error.
     *
     * @throws \Throwable
     */
    public function sendOrFail(Mailable $mailable, string $to): void;
}
