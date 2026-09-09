<?php

namespace App\Services\Contact;

use App\Mail\Contact\ContactAutoReply;
use App\Mail\Contact\ContactNotification;
use App\Models\Contact\ContactSubmission;
use App\Support\Phone;
use App\Support\Settings;
use DomainException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactService
{
    /** @return array<string, mixed> */
    public function pageData(): array
    {
        $company = Settings::group('company');
        $contact = Settings::merged('contact');

        $lat = $company['latitude'] ?? null;
        $lng = $company['longitude'] ?? null;
        $phone = $company['phone'] ?? null;

        return [
            'enabled' => Settings::bool('contact.enabled', true),
            'email' => $company['email'] ?? null,
            'phone' => $phone,
            'tel_href' => Phone::href($phone),
            'address' => $company['address'] ?? null,
            'heading' => $contact['heading'] ?: 'Bize yazın',
            'intro' => $contact['intro'] ?? '',
            'privacy_required' => Settings::bool('contact.privacy_required', true),
            'privacy_html' => str_replace(
                '{kvkk}',
                '<a href="'.e(route('kvkk')).'" target="_blank" rel="noopener noreferrer">KVKK Aydınlatma Metni</a>',
                e((string) ($contact['privacy_text'] ?? '')),
            ),
            'map_embed' => (filled($lat) && filled($lng))
                ? 'https://maps.google.com/maps?q='.rawurlencode($lat.','.$lng).'&z=15&output=embed&hl=tr'
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, ?string $ip, ?string $userAgent): string
    {
        if (! Settings::bool('contact.enabled', true)) {
            throw new DomainException('İletişim formu şu anda kapalıdır.');
        }

        if (filled($data['website'] ?? null)) {
            return $this->successMessage();
        }

        $submission = ContactSubmission::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'ip_address' => $ip,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 512) : null,
        ]);

        $this->deliver($submission);

        return $this->successMessage();
    }

    private function successMessage(): string
    {
        return (string) (Settings::merged('contact')['success_message'] ?: 'Mesajınız alındı.');
    }

    private function deliver(ContactSubmission $submission): void
    {
        $contact = Settings::merged('contact');
        $to = filled($contact['to_email'] ?? null)
            ? $contact['to_email']
            : Settings::get('company.email');

        if (blank($to)) {
            Log::warning('Contact mail skipped: no recipient.', ['id' => $submission->id]);

            return;
        }

        $subject = $this->interpolate((string) ($contact['subject'] ?: 'İletişim formu: {name}'), $submission);

        try {
            $mail = Mail::to($to);

            if (filled($contact['cc_email'] ?? null)) {
                $mail->cc($contact['cc_email']);
            }

            $mail->send(new ContactNotification($submission, $subject));
        } catch (Throwable $e) {
            Log::error('Contact notification failed.', [
                'id' => $submission->id,
                'message' => $e->getMessage(),
            ]);
        }

        if (! Settings::bool('contact.auto_reply_enabled')) {
            return;
        }

        try {
            Mail::to($submission->email)->send(new ContactAutoReply(
                $submission,
                $this->interpolate((string) ($contact['auto_reply_subject'] ?: 'Mesajınız bize ulaştı'), $submission),
                $this->interpolate((string) ($contact['auto_reply_body'] ?? ''), $submission),
            ));
        } catch (Throwable $e) {
            Log::error('Contact auto-reply failed.', [
                'id' => $submission->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function interpolate(string $template, ContactSubmission $submission): string
    {
        return strtr($template, [
            '{name}' => $submission->name,
            '{email}' => $submission->email,
            '{phone}' => (string) ($submission->phone ?? ''),
            '{message}' => $submission->message,
        ]);
    }
}
