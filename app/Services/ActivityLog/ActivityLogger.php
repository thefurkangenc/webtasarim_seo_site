<?php

namespace App\Services\ActivityLog;

use App\Jobs\ResolveActivityLocation;
use App\Models\ActivityLog\ActivityLog;
use App\Support\UserAgent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Denetim kaydı yazıcısı.
 *
 * Konteynerde singleton olarak tutulur (bkz. AppServiceProvider): istek
 * bağlamı — user-agent ayrıştırması, IP, istek kimliği — bir kez hesaplanıp
 * o istekteki tüm loglarda yeniden kullanılır. Aynı istekte oluşan loglar
 * ortak bir request_id taşır, böylece "bunlar birlikte oldu" bağı kurulur.
 *
 * Doğrudan çağırmak yerine genelde şunlar kullanılır:
 *   - App\Models\Concerns\LogsActivity trait'i (model olayları)
 *   - App\Support\Activity::record() (model dışı olaylar)
 */
class ActivityLogger
{
    /** @var array<string, mixed>|null İstek başına bir kez hesaplanan bağlam. */
    private ?array $context = null;

    /**
     * @param  array<string, mixed>  $properties  ['old' => [...], 'new' => [...]]
     * @param  array<int, string>  $changedKeys
     */
    public function log(
        string $logName,
        string $event,
        string $description,
        ?Model $subject = null,
        ?string $subjectLabel = null,
        array $properties = [],
        array $changedKeys = [],
        ?string $severity = null,
        ?Model $causer = null,
    ): ?ActivityLog {
        if (! config('activity-log.enabled')) {
            return null;
        }

        $causer ??= Auth::user();
        $context = $this->context();

        $log = ActivityLog::create([
            'log_name' => $logName,
            'event' => $event,
            'severity' => $severity ?? (config("activity-log.events.{$event}.severity") ?? 'info'),
            // Açıklama kolonu 500 karakter; uzun başlıklar taşmasın.
            'description' => Str::limit($description, 490),

            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel ? Str::limit($subjectLabel, 250) : null,

            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'causer_name' => $causer?->name,
            'causer_email' => $causer?->email,
            'causer_roles' => $this->rolesOf($causer),

            'properties' => $properties ?: null,
            'changed_keys' => $changedKeys ?: null,

            ...$context,
            // Bağlamdan sonra geliyor: kaydın hangi durumda oluşturulacağı
            // IP'ye bağlı ve INSERT'e dahil edilmeli — sonradan UPDATE
            // atmak her log için fazladan bir sorgu demekti.
            'geo_status' => $this->geoStatus($context['ip_address'] ?? null),
        ]);

        if ($log->geo_status === 'pending') {
            ResolveActivityLocation::dispatch($log->id, $log->ip_address);
        }

        return $log;
    }

    /**
     * Konum çözümlemesi bu kayıt için anlamlı mı?
     * Özel/yerel adresler (127.0.0.1, 192.168.x.x) dışarıya sorulmaz.
     */
    private function geoStatus(?string $ip): string
    {
        if (! config('activity-log.geo.enabled') || ! $ip) {
            return 'skipped';
        }

        $isPublic = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        );

        return $isPublic ? 'pending' : 'skipped';
    }

    /**
     * İstek bağlamı. Konsolda (artisan, kuyruk işçisi) HTTP isteği yoktur —
     * o durumda ağ alanları boş kalır ve kaynak "konsol" olarak işaretlenir.
     *
     * @return array<string, mixed>
     */
    private function context(): array
    {
        if ($this->context !== null) {
            return $this->context;
        }

        if (app()->runningInConsole()) {
            return $this->context = [
                'method' => 'CLI',
                'url' => 'artisan',
                'request_id' => (string) Str::uuid(),
            ];
        }

        return $this->context = $this->fromRequest(request());
    }

    /**
     * Bir HTTP isteğinden bağlam çıkarır.
     *
     * context()'ten ayrı bir metot: runningInConsole() artisan içinde her
     * zaman true döndüğü için bu dal tinker'dan test edilemiyordu; ayrı
     * olunca istek verilerek doğrudan sınanabiliyor.
     *
     * @return array<string, mixed>
     */
    private function fromRequest(Request $request): array
    {
        $agent = UserAgent::parse($request->userAgent());

        return [
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'browser' => $agent['browser'],
            'browser_version' => $agent['browser_version'],
            'platform' => $agent['platform'],
            'platform_version' => $agent['platform_version'],
            'device_type' => $agent['device_type'],
            'device_brand' => $agent['device_brand'],
            'is_bot' => $agent['is_bot'],

            'method' => $request->method(),
            'url' => Str::limit($request->fullUrl(), 990, ''),
            'route_name' => $request->route()?->getName(),
            'referer' => Str::limit((string) $request->headers->get('referer'), 990, '') ?: null,
            'locale' => app()->getLocale(),
            // Oturum her zaman başlatılmış olmayabilir (API/stateless istekler).
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'request_id' => (string) Str::uuid(),
        ];
    }

    /** @return array<int, string> */
    private function rolesOf(?Model $causer): array
    {
        if (! $causer || ! method_exists($causer, 'getRoleNames')) {
            return [];
        }

        return $causer->getRoleNames()->all();
    }
}
