<?php

namespace App\Mail\Lead;

use App\Models\Lead\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Panelden bir talebe verilen e-posta yanıtı. */
class LeadReply extends Mailable
{
    public function __construct(
        public Lead $lead,
        public string $subjectLine,
        public string $bodyText,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lead.reply');
    }
}
