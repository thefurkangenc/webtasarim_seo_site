<?php

namespace App\Mail\Health;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HealthAlert extends Mailable
{
    /** @param array<string, mixed> $report */
    public function __construct(public array $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->report['counts']['critical'].' kritik sistem sorunu — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.health.alert',
            with: [
                'checks' => array_filter(
                    $this->report['checks'],
                    fn (array $check) => in_array($check['status'], ['critical', 'warning'], true),
                ),
                'panelUrl' => route('admin.health.index'),
            ],
        );
    }
}
