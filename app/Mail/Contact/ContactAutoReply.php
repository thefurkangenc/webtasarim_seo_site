<?php

namespace App\Mail\Contact;

use App\Models\Contact\ContactSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactAutoReply extends Mailable
{
    public function __construct(
        public ContactSubmission $submission,
        public string $subjectLine,
        public string $bodyText,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact.auto-reply',
        );
    }
}
