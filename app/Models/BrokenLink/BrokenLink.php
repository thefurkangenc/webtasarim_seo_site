<?php

namespace App\Models\BrokenLink;

use App\Models\Blog\Blog;
use App\Models\Concerns\LogsActivity;
use App\Models\Hero\Hero;
use App\Models\Menu\MenuItem;
use App\Models\Page\Page;
use App\Models\Project\Project;
use App\Models\Service\Service;
use App\Support\UrlPath;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tarama sonucu bulunan çalışmayan bir adres. Satır "şu içerikteki şu adres
 * çalışmıyor" demektir; tarama tekrarlandığında aynı satır güncellenir.
 *
 * Tarama kayıtları toplu yazdığı için model olayları tetiklenmez — o yüzden
 * denetim kaydına taramanın kendisi App\Support\Activity ile bir özet olarak
 * düşer. Panelden yapılan yok sayma/silme normal model olaylarından loglanır.
 */
#[Fillable([
    'source_type', 'source_id', 'source_label', 'source_field',
    'url', 'url_hash', 'kind', 'scope', 'status_code', 'reason', 'message',
    'ignored', 'first_seen_at', 'last_checked_at',
])]
class BrokenLink extends Model
{
    use LogsActivity;

    /** Kaynak kaydın panel düzenleme adresi — kaynak türüne göre değişir. */
    private const EDIT_ROUTES = [
        Page::class => 'admin.page.edit',
        Blog::class => 'admin.blog.edit',
        Service::class => 'admin.service.edit',
        Project::class => 'admin.project.edit',
        MenuItem::class => 'admin.menu.index',
        Hero::class => 'admin.hero.index',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ignored' => 'boolean',
            'status_code' => 'integer',
            'first_seen_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('ignored', false);
    }

    public static function hash(string $url): string
    {
        return sha1($url);
    }

    public function sourceTypeLabel(): string
    {
        return config("broken-links.sources.{$this->source_type}", 'Diğer');
    }

    public function reasonLabel(): string
    {
        return config("broken-links.reasons.{$this->reason}", $this->reason);
    }

    public function kindLabel(): string
    {
        return config("broken-links.kinds.{$this->kind}", $this->kind);
    }

    /**
     * "Kaynağı düzenle" adresi. Menü ve tanıtım alanının kayıt bazlı düzenleme
     * ekranı yok; ikisi de kendi yönetim sayfasına gider.
     */
    public function editUrl(): ?string
    {
        $route = self::EDIT_ROUTES[$this->source_type] ?? null;

        if (! $route) {
            return null;
        }

        return in_array($this->source_type, [MenuItem::class, Hero::class], true)
            ? route($route)
            : route($route, $this->source_id);
    }

    /**
     * İç bir adres için yönlendirme kaynağı olarak kullanılacak yol.
     * Dış adreste yönlendirme anlamsız, eksik görselde ise çözüm değil
     * (görsel yeniden yüklenmeli) — ikisinde de null döner.
     */
    public function redirectPath(): ?string
    {
        if ($this->scope !== 'internal' || $this->kind !== 'link') {
            return null;
        }

        $path = UrlPath::normalize(parse_url($this->url, PHP_URL_PATH) ?: '');

        return $path !== '' ? $path : null;
    }

    public function activityLogName(): string
    {
        return 'broken-link';
    }

    public function activityLabel(): string
    {
        return $this->url;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'kind' => $this->kind,
            'kind_label' => $this->kindLabel(),
            'scope' => $this->scope,
            'status_code' => $this->status_code,
            'reason' => $this->reason,
            'reason_label' => $this->reasonLabel(),
            'message' => $this->message,
            'source_label' => $this->source_label,
            'source_type_label' => $this->sourceTypeLabel(),
            'source_field' => $this->source_field,
            'edit_url' => $this->editUrl(),
            'redirect_path' => $this->redirectPath(),
            'ignored' => $this->ignored,
            'first_seen_at' => $this->first_seen_at?->format('d.m.Y H:i'),
            'last_checked_at' => $this->last_checked_at?->format('d.m.Y H:i'),
        ];
    }
}
