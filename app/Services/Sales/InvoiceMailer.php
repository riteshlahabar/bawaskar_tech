<?php

namespace App\Services\Sales;

use App\Contracts\Mail\MailSenderContract;
use App\Contracts\Sales\InvoiceMailerContract;
use App\Contracts\Sales\SalesDocumentDataContract;
use App\Contracts\Sales\SalesDocumentPdfContract;
use App\Mail\InvoiceMail;
use App\Models\MailSetting;
use App\Models\Sales\Invoice;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * SRP: turn one Invoice row into a sent e-mail.
 *
 * The payload and the PDF come from the same two services the admin Download
 * PDF button uses, so the attachment is byte-for-byte the document the chosen
 * design produces - there is no second invoice renderer here.
 */
final class InvoiceMailer implements InvoiceMailerContract
{
    public function __construct(
        private readonly SalesDocumentDataContract $documents,
        private readonly SalesDocumentPdfContract $pdf,
        private readonly MailSenderContract $mail,
    ) {}

    public function recipient(Invoice $invoice): ?string
    {
        $order = $invoice->order;

        if ($order === null) {
            return null;
        }

        $email = $order->order_type === 'dealer'
            ? $order->dealer?->email
            : $order->customer?->email;

        return filled($email) ? (string) $email : null;
    }

    public function send(Invoice $invoice): bool
    {
        // Checked before anything is built: rendering the PDF of an invoice
        // nobody can receive is the most expensive way to do nothing.
        if (! MailSetting::current()->canSend() || $this->recipient($invoice) === null) {
            return false;
        }

        try {
            [$mailable, $to] = $this->build($invoice);

            return $this->mail->send($mailable, $to);
        } catch (Throwable $failure) {
            Log::warning('Invoice e-mail skipped for invoice '.$invoice->getKey().': '.$failure->getMessage());

            return false;
        }
    }

    public function sendOrFail(Invoice $invoice): void
    {
        if (! MailSetting::current()->canSend()) {
            throw new RuntimeException('E-mail is switched off. Turn it on in Settings → Mail Settings first.');
        }

        $to = $this->recipient($invoice);

        if ($to === null) {
            throw new RuntimeException('This buyer has no e-mail address on their account.');
        }

        [$mailable] = $this->build($invoice);

        $this->mail->sendOrFail($mailable, $to);
    }

    /**
     * @return array{0: InvoiceMail, 1: string|null}
     */
    private function build(Invoice $invoice): array
    {
        $document = $this->documents->forDocument('invoice', $invoice->getKey());

        return [
            new InvoiceMail($document, $this->pdf->render($document), $this->pdf->filename($document)),
            $this->recipient($invoice),
        ];
    }
}
