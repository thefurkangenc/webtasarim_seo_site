<?php

namespace App\Models\ActivityLog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Tek bir denetim kaydı. Yazma işi App\Services\ActivityLog\ActivityLogger'a
 * aittir; bu sınıf okuma, biçimleme ve filtreleme sağlar.
 *
 * Kayıtlar değiştirilmez: UPDATED_AT kapalıdır ve uygulama bir logu asla
 * güncellemez — tek istisna kuyruktaki konum çözümlemesinin boş coğrafya
 * kolonlarını doldurmasıdır.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'changed_keys' => 'array',
            'causer_roles' => 'array',
            'is_bot' => 'boolean',
            'created_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /* ------------------------------------------------------------------ *
     * Filtreler — liste ekranı ve modül bazlı modal bunları kullanır
     * ------------------------------------------------------------------ */

    public function scopeForModule(Builder $query, ?string $module): Builder
    {
        return $query->when($module, fn (Builder $q) => $q->where('log_name', $module));
    }

    /** Belirli bir kaydın geçmişi — satır menüsündeki "Geçmiş" bunu çağırır. */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    /* ------------------------------------------------------------------ *
     * Arayüz yardımcıları
     * ------------------------------------------------------------------ */

    /** @return array{label: string, icon: string, color: string, severity: string} */
    public function eventMeta(): array
    {
        return config("activity-log.events.{$this->event}") ?? [
            'label' => $this->event,
            'icon' => 'bolt',
            'color' => 'gray',
            'severity' => 'info',
        ];
    }

    /** @return array{label: string, icon: string} */
    public function moduleMeta(): array
    {
        return config("activity-log.modules.{$this->log_name}") ?? [
            'label' => $this->log_name,
            'icon' => 'category',
        ];
    }

    /** @return array{label: string, color: string} */
    public function severityMeta(): array
    {
        return config("activity-log.severities.{$this->severity}")
            ?? ['label' => $this->severity, 'color' => 'gray'];
    }

    /**
     * Tarayıcı/işletim sistemi ikonlarının dosya adı. Karşılığı yoksa null
     * döner ve arayüz genel bir ikona düşer.
     */
    public function browserIcon(): ?string
    {
        return self::iconFor('browsers', $this->browser);
    }

    public function platformIcon(): ?string
    {
        return self::iconFor('platforms', $this->platform);
    }

    /**
     * Etiket ("Google Chrome") -> dosya adı ("chrome.svg"). Eşleme burada
     * tutulur çünkü ikon dosyaları arayüze aittir, ayrıştırıcıya değil.
     */
    private static function iconFor(string $group, ?string $label): ?string
    {
        if (! $label) {
            return null;
        }

        // Microsoft Edge ve Internet Explorer bilinçli olarak yok: kullandığımız
        // CC0 ikon seti (simple-icons) Microsoft'un talebiyle bu logoları
        // kaldırdı. Karşılığı olmayan tarayıcılar arayüzde marka renkli bir
        // harf rozetine düşer — hiçbir zaman boş görünmez.
        $map = [
            'browsers' => [
                'Google Chrome' => 'chrome',
                'Mozilla Firefox' => 'firefox',
                'Safari' => 'safari',
                'Opera' => 'opera',
                'Samsung Internet' => 'samsung',
                'Brave' => 'brave',
                'Vivaldi' => 'vivaldi',
                'Yandex Browser' => 'yandex',
            ],
            'platforms' => [
                'Windows' => 'windows',
                'macOS' => 'macos',
                'iOS' => 'ios',
                'Android' => 'android',
                'Ubuntu' => 'ubuntu',
                'Linux' => 'linux',
                'ChromeOS' => 'chromeos',
            ],
        ];

        $file = $map[$group][$label] ?? null;

        return $file ? "admin/assets/images/icons/{$group}/{$file}.svg" : null;
    }

    /** Cihaz tipine karşılık gelen material ikon adı. */
    public function deviceIcon(): string
    {
        return match ($this->device_type) {
            'mobile' => 'smartphone',
            'tablet' => 'tablet_mac',
            'desktop' => 'computer',
            'bot' => 'smart_toy',
            default => 'devices_other',
        };
    }

    /** "Gaziantep, Türkiye" — eksik parçalar atlanır. */
    public function locationLabel(): ?string
    {
        $parts = array_filter([$this->city, $this->country]);

        return $parts ? implode(', ', $parts) : null;
    }

    /**
     * Değişiklik diff'i: [alan => ['old' => ..., 'new' => ...]] biçiminde,
     * detay modalındaki tablo için düzleştirilmiş hali.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function changes(): array
    {
        $old = $this->properties['old'] ?? [];
        $new = $this->properties['new'] ?? [];

        $keys = array_unique([...array_keys($old), ...array_keys($new)]);
        sort($keys);

        $changes = [];

        foreach ($keys as $key) {
            $changes[$key] = [
                'old' => $old[$key] ?? null,
                'new' => $new[$key] ?? null,
            ];
        }

        return $changes;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'event_meta' => $this->eventMeta(),
            'module' => $this->log_name,
            'module_meta' => $this->moduleMeta(),
            'severity' => $this->severity,
            'severity_meta' => $this->severityMeta(),
            'description' => $this->description,

            'subject_label' => $this->subject_label,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,

            'causer_name' => $this->causer_name,
            'causer_email' => $this->causer_email,
            'causer_roles' => $this->causer_roles ?? [],

            'ip_address' => $this->ip_address,
            'browser' => $this->browser,
            'browser_version' => $this->browser_version,
            'browser_icon' => $this->browserIcon() ? asset($this->browserIcon()) : null,
            'platform' => $this->platform,
            'platform_version' => $this->platform_version,
            'platform_icon' => $this->platformIcon() ? asset($this->platformIcon()) : null,
            'device_type' => $this->device_type,
            'device_brand' => $this->device_brand,
            'device_icon' => $this->deviceIcon(),
            'is_bot' => $this->is_bot,

            'location' => $this->locationLabel(),
            'country_code' => $this->country_code,

            'changed_keys' => $this->changed_keys ?? [],
            'created_at' => $this->created_at?->format('d.m.Y H:i:s'),
            'created_for_humans' => $this->created_at?->diffForHumans(),
        ];
    }
}
