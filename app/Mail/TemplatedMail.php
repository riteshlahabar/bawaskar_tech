<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Support\Mail\RenderedTemplate;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Base for every e-mail whose wording comes from Settings -> Email Templates.
 *
 * It owns the two things all of them share: the subject is the template's,
 * and the view is handed the rendered paragraphs plus the company row that
 * the shared layout's header and footer need. Each subclass only names its
 * view and adds whatever data block belongs under the words.
 */
abstract class TemplatedMail extends Mailable
{
    public function __construct(protected readonly RenderedTemplate $template) {}

    abstract protected function templateView(): string;

    /**
     * Data the subclass's own block needs, on top of the shared keys.
     *
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return [];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->template->subject);
    }

    public function content(): Content
    {
        return new Content(view: $this->templateView(), with: $this->data() + [
            'paragraphs' => $this->template->paragraphs(),
            'company' => CompanySetting::query()->first(),
            'subjectLine' => $this->template->subject,
        ]);
    }
}
