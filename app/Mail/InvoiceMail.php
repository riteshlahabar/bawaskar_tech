<?php

namespace App\Mail;

use App\Support\Mail\RenderedTemplate;
use Illuminate\Mail\Mailables\Attachment;

/**
 * The Sale Invoice covering mail, with the real GST PDF attached.
 *
 * It is handed the finished payload and the finished PDF bytes - building
 * either of those is InvoiceMailer's job, so this class never touches the
 * database and can be rendered in a test from a plain array.
 */
class InvoiceMail extends TemplatedMail
{
    /**
     * @param  array<string, mixed>  $document  payload from SalesDocumentDataContract
     */
    public function __construct(
        RenderedTemplate $template,
        private readonly array $document,
        private readonly string $pdf,
        private readonly string $pdfName,
    ) {
        parent::__construct($template);
    }

    protected function templateView(): string
    {
        return 'emails.invoice';
    }

    protected function data(): array
    {
        return $this->document;
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
