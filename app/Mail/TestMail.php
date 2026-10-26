<?php

namespace App\Mail;

use App\Models\CompanySetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent by the "Send Test Email" button in Settings -> Mail Settings, to prove
 * the saved SMTP credentials actually work before anything real depends on them.
 */
class TestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Test email from '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: [
                'company' => CompanySetting::query()->first(),
                'subjectLine' => 'Test email',
                'sentAt' => now()->format('d M Y, g:i A'),
            ]
        );
    }
}
