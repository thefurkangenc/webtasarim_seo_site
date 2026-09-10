<?php

namespace App\Services\Menu;

use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Menü yöneticisinin ön yüz tarafı: bir konum anahtarını (header,
 * footer_primary...) çözülmüş, iç içe bir diziye çevirir.
 *
 * URL'i çözülemeyen öğeler (yayından kalkmış sayfa, silinmiş kayıt, tanımsız
 * route) alt öğeleriyle birlikte atlanır — ön yüzde asla kırık bağlantı çıkmaz.
 *
 * Sonuç konum bazında önbelleğe alınır; herhangi bir menü/öğe değişiminde
 * App\Observers\MenuObserver önbelleği temizler.
 */
class MenuRenderer
{
    private const CACHE_PREFIX = 'menu.rendered.';

    /**
     * @return array<int, array<string, mixed>> Her düğüm:
     *                                          label, url, target, external (bool), active (bool), children[]
     */
    public function render(string $key): array
    {
        $tree = Cache::rememberForever(self::CACHE_PREFIX.$key, fn () => $this->build($key));

        // "Aktif" durumu isteğe bağlıdır, önbelleğe alınmaz — her istekte
        // geçerli adrese göre yeniden işaretlenir.
        return $this->markActive($tree);
    }

    public function heading(string $key): ?string
    {
        return Menu::where('key', $key)->value('title')
            ?? config("menus.locations.{$key}.title");
    }

    public static function forget(?string $key = null): void
    {
        $keys = $key ? [$key] : array_keys(config('menus.locations', []));

        foreach ($keys as $k) {
            Cache::forget(self::CACHE_PREFIX.$k);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function build(string $key): array
    {
        $menu = Menu::where('key', $key)->first();

        if (! $menu) {
            return [];
        }

        $items = $menu->items()
            ->where('status', true)
            ->with('linkable')
            ->get()
            ->groupBy(fn (MenuItem $item) => $item->parent_id ?? 0);

        return $this->buildNodes($items, 0);
    }

    /**
     * @param  Collection<int, MenuItem>  $grouped
     * @return array<int, array<string, mixed>>
     */
    private function buildNodes(Collection $grouped, int $parentId): array
    {
        $nodes = [];

        foreach ($grouped->get($parentId, collect()) as $item) {
            $url = $item->resolveUrl();

            // Adresi çözülemeyen öğe alt ağacıyla birlikte düşer.
            if ($url === null) {
                continue;
            }

            $nodes[] = [
                'label' => $item->resolveLabel(),
                'url' => $url,
                'target' => $item->target,
                'external' => $this->isExternal($url),
                'path' => $this->pathOf($url),
                'children' => $this->buildNodes($grouped, $item->id),
            ];
        }

        return $nodes;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function markActive(array $nodes): array
    {
        $current = trim(request()->path(), '/');

        return array_map(function (array $node) use ($current) {
            $node['children'] = $this->markActive($node['children']);

            $selfActive = $node['path'] !== null && $node['path'] === $current;
            $childActive = collect($node['children'])->contains('active', true);

            $node['active'] = $selfActive || $childActive;

            return $node;
        }, $nodes);
    }

    private function isExternal(string $url): bool
    {
        if (! str_contains($url, '://')) {
            return false;
        }

        return parse_url($url, PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST);
    }

    /** İç bağlantının site köküne göre yolu; dış bağlantıda null. */
    private function pathOf(string $url): ?string
    {
        if ($this->isExternal($url)) {
            return null;
        }

        return trim((string) parse_url($url, PHP_URL_PATH), '/');
    }
}
