<?php

namespace App\Models\Menu;

use App\Contracts\LinksToPublicPage;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Route;

/**
 * Bir menü öğesi. Üç bağlantı tipinden biri:
 *
 *   url      → elle girilen adres (mutlak ya da göreli)
 *   route    → parametresiz ön yüz route'u (config/menus.php > routes)
 *   linkable → bir kayda bağlı (Page/Service/Blog); URL render anında çözülür
 *
 * Ağaç `parent_id` ile kurulur, en fazla config('menus.max_depth') derinlik.
 */
#[Fillable([
    'menu_id', 'parent_id', 'label', 'link_type', 'url', 'route_name',
    'linkable_type', 'linkable_id', 'target', 'status', 'sort_order',
])]
class MenuItem extends Model
{
    use HasSortOrder, LogsActivity;

    public const TYPE_URL = 'url';

    public const TYPE_ROUTE = 'route';

    public const TYPE_LINKABLE = 'linkable';

    public const TYPES = [
        self::TYPE_URL => 'Özel bağlantı',
        self::TYPE_ROUTE => 'Hazır bağlantı',
        self::TYPE_LINKABLE => 'Kayda bağlı',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Sıralama menü + üst öğe içinde tekil; her dal kendi içinde sıralanır. */
    protected function sortOrderScope(Builder $query): Builder
    {
        return $query->where('menu_id', $this->menu_id)->where('parent_id', $this->parent_id);
    }

    /** Loglarda "Üst Menü" gibi görünür, ham "MenuItem" değil. */
    public function activityLogName(): string
    {
        return 'menu';
    }

    /**
     * Öğenin çözülmüş ön yüz adresi. Bağlı kayıt yayında değilse ya da hazır
     * route tanımsızsa null döner — MenuRenderer o öğeyi (ve alt öğelerini)
     * atlar.
     */
    public function resolveUrl(): ?string
    {
        return match ($this->link_type) {
            self::TYPE_ROUTE => $this->route_name && Route::has($this->route_name)
                ? route($this->route_name)
                : null,
            self::TYPE_LINKABLE => $this->linkable instanceof LinksToPublicPage
                ? $this->linkable->publicUrl()
                : null,
            default => $this->url ?: null,
        };
    }

    /** Etiket boş bırakıldıysa bağlı kaydın adına düşülür. */
    public function resolveLabel(): string
    {
        if (filled($this->label)) {
            return $this->label;
        }

        if ($this->link_type === self::TYPE_LINKABLE && $this->linkable instanceof LinksToPublicPage) {
            return $this->linkable->publicLinkLabel();
        }

        if ($this->link_type === self::TYPE_ROUTE) {
            return config("menus.routes.{$this->route_name}", $this->route_name ?? '');
        }

        return $this->url ?? '';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->link_type] ?? $this->link_type;
    }

    /** Panelde öğe kartında gösterilen kısa hedef açıklaması. */
    public function targetSummary(): string
    {
        return match ($this->link_type) {
            self::TYPE_ROUTE => config("menus.routes.{$this->route_name}", (string) $this->route_name),
            self::TYPE_LINKABLE => $this->linkableSummary(),
            default => (string) $this->url,
        };
    }

    private function linkableSummary(): string
    {
        $key = collect(config('menus.linkables', []))
            ->search(fn ($config) => $config['model'] === $this->linkable_type);

        $label = $key ? config("menus.linkables.{$key}.label") : 'Kayıt';

        return $this->linkable instanceof LinksToPublicPage
            ? "{$label}: {$this->linkable->publicLinkLabel()}"
            : "{$label} (silinmiş)";
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'label' => $this->label,
            'resolved_label' => $this->resolveLabel(),
            'link_type' => $this->link_type,
            'type_label' => $this->typeLabel(),
            'url' => $this->url,
            'route_name' => $this->route_name,
            'linkable_type' => $this->linkable_type,
            'linkable_id' => $this->linkable_id,
            'target' => $this->target,
            'target_summary' => $this->targetSummary(),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
        ];
    }
}
