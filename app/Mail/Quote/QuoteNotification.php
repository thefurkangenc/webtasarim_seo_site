<?php

namespace App\Mail\Quote;

use App\Models\Lead\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuoteNotification extends Mailable
{
    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Teklif talebi: {$this->lead->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.quote.notification');
    }
}
