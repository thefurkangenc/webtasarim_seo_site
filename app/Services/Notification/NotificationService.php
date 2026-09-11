<?php

namespace App\Services\Notification;

use App\Models\Ai\AiGeneration;
use App\Models\BrokenLink\BrokenLink;
use App\Models\Lead\Lead;
use App\Models\Notification\NotificationRead;
use App\Models\Subscriber\Subscriber;
use App\Models\User;
use App\Services\Health\HealthService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Header'daki bildirim merkezi.
 *
 * Bildirimler SAKLANMAZ, canlı durumdan türetilir: okunmamış talep, kritik
 * sistem kontrolü, kırık link, başarısız kuyruk işi… Böylece bir sorun
 * çözülünce bildirimi de kendiliğinden kaybolur — ayrı bir tabloyu senkron
 * tutmak gerekmez ve "düzelttim ama bildirim duruyor" durumu oluşmaz.
 *
 * Saklanan tek şey kullanıcının NEYİ GÖRDÜĞÜ (`notification_reads`). Her
 * bildirimin kararlı bir anahtarı vardır ("lead:42"), okundu işareti o
 * anahtara yazılır.
 *
 * Yetki: her öğe bir izinle etiketlenir ve kullanıcı o izne sahip değilse
 * listeye hiç girmez — bildirim ucu yetki ara katmanından muaftır.
 */
class NotificationService
{
    /** Listede gösterilecek en fazla öğe sayısı. */
    private const LIMIT = 12;

    public function __construct(private readonly HealthService $health) {}

    /**
     * @return array{items: list<array<string, mixed>>, unread: int}
     */
    public function feed(User $user): array
    {
        $items = collect($this->candidates($user))
            ->filter(fn (array $item) => $this->allows($user, $item['permission']))
            ->sortByDesc('at')
            ->take(self::LIMIT)
            ->values();

        $read = NotificationRead::where('user_id', $user->id)
            ->whereIn('key', $items->pluck('key'))
            ->pluck('key')
            ->all();

        $items = $items->map(fn (array $item) => [
            'key' => $item['key'],
            'title' => $item['title'],
            'body' => $item['body'],
            'url' => $item['url'],
            'icon' => $item['icon'],
            'tone' => $item['tone'],
            'ago' => $item['at']->diffForHumans(),
            'read' => in_array($item['key'], $read, true),
        ])->all();

        return [
            'items' => $items,
            'unread' => collect($items)->where('read', false)->count(),
        ];
    }

    /** Tek bir bildirimi okundu işaretler. Zaten okunmuşsa hiçbir şey olmaz. */
    public function markRead(User $user, string $key): void
    {
        NotificationRead::firstOrCreate(
            ['user_id' => $user->id, 'key' => $key],
            ['created_at' => now()],
        );
    }

    /**
     * Tümünü okundu işaretler. Yalnızca ŞU AN görünen öğeler işaretlenir —
     * ileride doğacak bir bildirimi şimdiden susturmak yanlış olurdu.
     */
    public function markAllRead(User $user): int
    {
        $keys = collect($this->feed($user)['items'])->where('read', false)->pluck('key');

        if ($keys->isEmpty()) {
            return 0;
        }

        NotificationRead::insertOrIgnore(
            $keys->map(fn (string $key) => [
                'user_id' => $user->id,
                'key' => $key,
                'created_at' => now(),
            ])->all(),
        );

        return $keys->count();
    }

    /**
     * Aday bildirimler. Her biri: key, title, body, url, icon, tone,
     * permission, at (Carbon).
     *
     * @return list<array<string, mixed>>
     */
    private function candidates(User $user): array
    {
        return [
            ...$this->leadItems(),
            ...$this->healthItems(),
            ...$this->failedJobItems(),
            ...$this->brokenLinkItems(),
            ...$this->subscriberItems(),
            ...$this->aiItems($user),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function leadItems(): array
    {
        return Lead::unread()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Lead $lead) => [
                'key' => "lead:{$lead->id}",
                'title' => $lead->name.' yeni bir mesaj gönderdi',
                'body' => $lead->subject ?: $lead->sourceLabel(),
                'url' => route('admin.lead.show', $lead),
                'icon' => 'mark_email_unread',
                'tone' => 'primary',
                'permission' => 'lead.index',
                'at' => $lead->created_at,
            ])
            ->all();
    }

    /**
     * Sistem kontrolleri. Rapor CACHE'TEN okunur — bildirim listesini açmak
     * SSL/SMTP/Google kontrollerini tetiklemez, aksi halde her menü açılışı
     * ağa çıkardı.
     *
     * @return list<array<string, mixed>>
     */
    private function healthItems(): array
    {
        $report = $this->health->cached();

        if (! $report) {
            return [];
        }

        $at = isset($report['checked_at']) ? Carbon::parse($report['checked_at']) : now();

        return collect($report['checks'])
            ->whereIn('status', ['critical', 'warning'])
            ->map(fn (array $check) => [
                // Anahtara durum da girer: uyarı kritiğe dönüşürse yeniden
                // okunmamış sayılır — durum değişimi yeni bir bilgidir.
                'key' => "health:{$check['key']}:{$check['status']}",
                'title' => $check['label'].($check['status'] === 'critical' ? ' — kritik' : ' — uyarı'),
                'body' => $check['message'] ?? '',
                'url' => route('admin.health.index'),
                'icon' => $check['status'] === 'critical' ? 'error' : 'warning',
                'tone' => $check['status'] === 'critical' ? 'danger' : 'warning',
                'permission' => 'health.index',
                'at' => $at,
            ])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function failedJobItems(): array
    {
        $last = DB::table('failed_jobs')->latest('failed_at')->first(['id', 'failed_at']);

        if (! $last) {
            return [];
        }

        $count = DB::table('failed_jobs')->count();

        return [[
            // Anahtar son başarısız işin kimliğini taşır: yeni bir iş
            // düşünce bildirim tekrar okunmamış olur.
            'key' => "failed-jobs:{$last->id}",
            'title' => $count.' kuyruk işi başarısız oldu',
            'body' => 'E-posta, yapay zeka üretimi ya da site haritası işleri tamamlanmamış olabilir.',
            'url' => route('admin.health.index'),
            'icon' => 'report',
            'tone' => 'danger',
            'permission' => 'health.index',
            'at' => Carbon::parse($last->failed_at),
        ]];
    }

    /** @return list<array<string, mixed>> */
    private function brokenLinkItems(): array
    {
        $count = BrokenLink::visible()->count();

        if ($count === 0) {
            return [];
        }

        $last = BrokenLink::visible()->max('last_checked_at');

        return [[
            'key' => "broken-links:{$count}",
            'title' => $count.' kırık bağlantı bulundu',
            'body' => 'İçeriklerinizdeki bazı adresler çalışmıyor.',
            'url' => route('admin.broken-link.index'),
            'icon' => 'link_off',
            'tone' => 'warning',
            'permission' => 'broken-link.index',
            'at' => $last ? Carbon::parse($last) : now(),
        ]];
    }

    /** @return list<array<string, mixed>> */
    private function subscriberItems(): array
    {
        return Subscriber::where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Subscriber $subscriber) => [
                'key' => "subscriber:{$subscriber->id}",
                'title' => 'Yeni bülten abonesi',
                'body' => $subscriber->email,
                'url' => route('admin.subscriber.index'),
                'icon' => 'mail',
                'tone' => 'success',
                'permission' => 'subscriber.index',
                'at' => $subscriber->created_at,
            ])
            ->all();
    }

    /**
     * Başarısız yapay zeka üretimleri — yalnızca KULLANICININ KENDİ işleri.
     * Başkasının üretim hatası onu ilgilendirmez.
     *
     * @return list<array<string, mixed>>
     */
    private function aiItems(User $user): array
    {
        return AiGeneration::where('status', AiGeneration::STATUS_FAILED)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(3))
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (AiGeneration $generation) => [
                'key' => "ai:{$generation->id}",
                'title' => 'Yapay zeka üretimi başarısız oldu',
                'body' => Str::limit((string) $generation->error, 90) ?: 'Sağlayıcı yanıt vermedi.',
                'url' => route('admin.ai-provider.index'),
                'icon' => 'auto_awesome',
                'tone' => 'danger',
                'permission' => 'ai-provider.index',
                'at' => $generation->created_at,
            ])
            ->all();
    }

    private function allows(User $user, ?string $permission): bool
    {
        return $permission === null || $user->hasRole('super-admin') || $user->can($permission);
    }
}
