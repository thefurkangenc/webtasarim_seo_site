<?php

namespace App\Mail\Contact;

use App\Models\Contact\ContactSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactNotification extends Mailable
{
    public function __construct(
        public ContactSubmission $submission,
        public string $subjectLine,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            replyTo: [
                new Address($this->submission->email, $this->submission->name),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact.notification',
        );
    }
}
