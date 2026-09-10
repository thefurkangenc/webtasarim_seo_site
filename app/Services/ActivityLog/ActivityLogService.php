<?php

namespace App\Services\ActivityLog;

use App\Models\ActivityLog\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Denetim kayıtlarının okunması. YAZMA işi ActivityLogger'a aittir; burada
 * kayıt oluşturan/güncelleyen hiçbir metot yoktur — loglar değiştirilmez.
 */
class ActivityLogService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn (ActivityLog $log) => $log->toPayload());
    }

    /**
     * Liste ekranının üstündeki özet kartlar. Filtreden BAĞIMSIZDIR —
     * her zaman genel durumu gösterir.
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'critical' => ActivityLog::where('severity', 'critical')->count(),
            'failed_logins' => ActivityLog::where('event', 'login_failed')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];
    }

    /**
     * Filtre açılır listelerinin içeriği — yalnızca gerçekten log üretmiş
     * modüller/olaylar gösterilir, boş seçenek sunulmaz.
     *
     * @return array<string, array<string, string>>
     */
    public function filterOptions(): array
    {
        $modules = ActivityLog::query()
            ->select('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->mapWithKeys(fn (string $key) => [
                $key => config("activity-log.modules.{$key}.label") ?? $key,
            ])
            ->all();

        $events = ActivityLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->mapWithKeys(fn (string $key) => [
                $key => config("activity-log.events.{$key}.label") ?? $key,
            ])
            ->all();

        return [
            'modules' => $modules,
            'events' => $events,
            'severities' => collect(config('activity-log.severities'))
                ->map(fn (array $meta) => $meta['label'])
                ->all(),
            'devices' => [
                'desktop' => 'Masaüstü',
                'mobile' => 'Mobil',
                'tablet' => 'Tablet',
                'bot' => 'Bot',
            ],
        ];
    }

    /**
     * Detay modalının gösterdiği tam kayıt: liste yükünde taşınmayan
     * user-agent, oturum, referer ve alan bazlı diff dahil.
     *
     * @return array<string, mixed>
     */
    public function detail(ActivityLog $log): array
    {
        return [
            ...$log->toPayload(),
            'user_agent' => $log->user_agent,
            'method' => $log->method,
            'url' => $log->url,
            'route_name' => $log->route_name,
            'referer' => $log->referer,
            'session_id' => $log->session_id,
            'request_id' => $log->request_id,
            'locale' => $log->locale,
            'region' => $log->region,
            'city' => $log->city,
            'country' => $log->country,
            'timezone' => $log->timezone,
            'isp' => $log->isp,
            'latitude' => $log->latitude,
            'longitude' => $log->longitude,
            'geo_status' => $log->geo_status,
            'changes' => $log->changes(),
            // Aynı istekte oluşan diğer kayıtlar — toplu işlemlerde
            // "bunlar birlikte oldu" bağını kurar.
            'related_count' => $log->request_id
                ? ActivityLog::where('request_id', $log->request_id)->where('id', '!=', $log->id)->count()
                : 0,
        ];
    }

    /**
     * Saklama süresini aşan kayıtları siler.
     * `php artisan activity-log:prune` bunu çağırır.
     */
    public function prune(int $days): int
    {
        return ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
    }

    /** @param  array<string, mixed>  $filters */
    private function query(array $filters): Builder
    {
        return ActivityLog::query()
            ->when($filters['search'] ?? null, fn (Builder $q, string $term) => $q->where(
                fn (Builder $q) => $q->where('description', 'like', "%{$term}%")
                    ->orWhere('subject_label', 'like', "%{$term}%")
                    ->orWhere('causer_name', 'like', "%{$term}%")
                    ->orWhere('causer_email', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
            ))
            ->when($filters['module'] ?? null, fn (Builder $q, string $v) => $q->where('log_name', $v))
            ->when($filters['event'] ?? null, fn (Builder $q, string $v) => $q->where('event', $v))
            ->when($filters['severity'] ?? null, fn (Builder $q, string $v) => $q->where('severity', $v))
            ->when($filters['device_type'] ?? null, fn (Builder $q, string $v) => $q->where('device_type', $v))
            ->when($filters['ip'] ?? null, fn (Builder $q, string $v) => $q->where('ip_address', $v))
            ->when($filters['causer_id'] ?? null, fn (Builder $q, $v) => $q->where('causer_id', $v))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $v) => $q->whereDate('created_at', '<=', $v))
            // Modül modalı ve satır bazlı "Geçmiş" bu ikisini kullanır.
            ->when($filters['subject_type'] ?? null, fn (Builder $q, string $v) => $q->where('subject_type', $v))
            ->when($filters['subject_id'] ?? null, fn (Builder $q, $v) => $q->where('subject_id', $v));
    }
}
