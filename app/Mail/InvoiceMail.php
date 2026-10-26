<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The Sale Invoice covering mail, with the real GST PDF attached.
 *
 * It is handed the finished payload and the finished PDF bytes - building
 * either of those is InvoiceMailer's job, so this class never touches the
 * database and can be rendered in a test from a plain array.
 */
class InvoiceMail extends Mailable
{
    /**
     * @param  array<string, mixed>  $document  payload from SalesDocumentDataContract
     */
    public function __construct(
        private readonly array $document,
        private readonly string $pdf,
        private readonly string $pdfName,
    ) {}

    public function envelope(): Envelope
    {
        $company = $this->document['company'] ?? null;

        return new Envelope(
            subject: 'Invoice '.($this->document['number'] ?? '').' from '.($company?->company_name ?: config('app.name'))
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: $this->document + ['subjectLine' => 'Sale Invoice '.($this->document['number'] ?? '')]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn (): string => $this->pdf, $this->pdfName)
                ->withMime('application/pdf'),
        ];
    }
}
