<?php

namespace App\Services\Report;

use App\Mail\Report\WeeklyReport;
use App\Models\Lead\Lead;
use App\Models\Subscriber\Subscriber;
use App\Services\Analytics\AnalyticsService;
use App\Support\Activity;
use App\Support\Settings;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Site sahibine pazartesi sabahı giden özet. Trafik GA4'ten, yeni mesajlar
 * gelen kutusundan, aboneler bülten listesinden. GA4 yoksa o bölüm atlanır.
 */
class WeeklyReportService
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function send(): bool
    {
        $to = filled(Settings::get('contact.to_email'))
            ? Settings::get('contact.to_email')
            : Settings::get('company.email');

        if (blank($to)) {
            return false;
        }

        $report = $this->build();

        Mail::to($to)->send(new WeeklyReport($report));

        Activity::record(
            logName: 'report',
            event: 'notified',
            description: 'Haftalık özet e-postası gönderildi',
            properties: ['new' => ['to' => $to]],
        );

        return true;
    }

    /** @return array<string, mixed> */
    public function build(): array
    {
        $from = now()->subDays(7)->startOfDay();

        return [
            'from' => $from->toIso8601String(),
            'from_label' => $from->translatedFormat('d F'),
            'to_label' => now()->translatedFormat('d F Y'),
            'analytics' => $this->analyticsSummary(),
            'leads' => [
                'new' => Lead::query()->where('created_at', '>=', $from)->count(),
                'unread' => Lead::query()->unread()->count(),
            ],
            'subscribers' => [
                'new' => Subscriber::query()->where('created_at', '>=', $from)->count(),
                'active' => Subscriber::query()->active()->count(),
            ],
            'dashboard_url' => route('admin.dashboard'),
            'leads_url' => route('admin.lead.index'),
        ];
    }

    /** @return array<string, mixed>|null */
    private function analyticsSummary(): ?array
    {
        if (! $this->analytics->configured()) {
            return null;
        }

        try {
            $summary = $this->analytics->summary(7);

            return [
                'kpis' => collect($summary['kpis'] ?? [])
                    ->whereIn('key', ['activeUsers', 'sessions', 'screenPageViews', 'newUsers'])
                    ->values()
                    ->all(),
                'top_pages' => array_slice($summary['top_pages'] ?? [], 0, 5),
            ];
        } catch (Throwable) {
            return null;
        }
    }
}
