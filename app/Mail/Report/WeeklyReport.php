<?php

namespace App\Mail\Report;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WeeklyReport extends Mailable
{
    /** @param array<string, mixed> $report */
    public function __construct(public array $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Haftalık site özeti · '.$this->report['from_label'].' – '.$this->report['to_label'],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.report.weekly');
    }
}
