<?php

namespace App\Services\Quote;

use App\Enums\LeadSource;
use App\Mail\Quote\QuoteNotification;
use App\Models\Lead\Lead;
use App\Models\Service\Service;
use App\Models\ServiceRegion\ServiceRegion;
use App\Support\Placeholder;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Hizmet detayındaki "Hızlı Teklif" formu. Talep gelen kutusuna
 * `source = quote` ile düşer; e-posta alınmaz, dönüş telefonladır.
 */
class QuoteService
{
    /**
     * Formun seçenekleri: yayındaki hizmetler. Sayfanın hizmeti seçili gelir.
     *
     * @return array<int, string>
     */
    public function serviceOptions(): array
    {
        return Service::query()
            ->where('status', Service::STATUS_PUBLISHED)
            ->orderBy('sort_order')
            ->get(['id', 'title'])
            // Başlıktaki bölge yer tutucuları ({{city}} vb.) atılır.
            ->mapWithKeys(fn (Service $service) => [$service->id => Placeholder::strip($service->title) ?: $service->title])
            ->all();
    }

    /** @param  array<string, mixed>  $data */
    public function submit(array $data, ?string $ip, ?string $userAgent, ?string $pageUrl = null): void
    {
        // Honeypot dolu: bot. Başarılı gibi dönülür, kayıt açılmaz.
        if (filled($data['website'] ?? null)) {
            return;
        }

        $service = Service::query()->findOrFail($data['service_id']);
        $region = filled($data['region_id'] ?? null) ? ServiceRegion::query()->find($data['region_id']) : null;
        $title = Placeholder::strip($service->title) ?: $service->title;

        $lead = Lead::query()->create([
            'source' => LeadSource::Quote,
            'name' => $data['company'],
            'phone' => $data['phone'],
            'subject' => $region ? "{$title} — {$region->placeholders()['region']}" : $title,
            'message' => filled($data['notes'] ?? null) ? $data['notes'] : 'Not bırakılmadı.',
            'ip_address' => $ip,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 512) : null,
            'page_url' => $pageUrl !== null ? mb_substr($pageUrl, 0, 500) : null,
        ]);

        // Bildirim alıcısı iletişim formuyla aynı; yoksa sessizce atlanır,
        // talep zaten panelde duruyor.
        $to = Settings::get('contact.to_email') ?: Settings::get('company.email');

        if (blank($to)) {
            return;
        }

        try {
            Mail::to($to)->send(new QuoteNotification($lead));
        } catch (Throwable $e) {
            Log::error('Quote notification failed.', ['id' => $lead->id, 'message' => $e->getMessage()]);
        }
    }
}
