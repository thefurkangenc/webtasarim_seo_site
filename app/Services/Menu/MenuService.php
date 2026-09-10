<?php

namespace App\Services\Menu;

use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use App\Support\Activity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menü yöneticisinin panel tarafı: öğe ekleme/güncelleme/silme ve ağacın
 * sürükle-bırak ile yeniden düzenlenmesi.
 *
 * Menü konumlarının kendisi (header, footer sütunları) sabittir —
 * config/menus.php'den MenuSeeder basar, burada oluşturma/silme yoktur.
 */
class MenuService
{
    /**
     * Panelin ihtiyaç duyduğu her şey: tüm menü konumları, seçili menünün
     * öğe ağacı ve "öğe ekle" modalının seçenekleri.
     *
     * @return array<string, mixed>
     */
    public function workspace(Menu $menu): array
    {
        return [
            'menu' => $menu->toPayload(),
            'menus' => Menu::withCount('items')->get()->map->toPayload()->all(),
            'tree' => $this->tree($menu),
            'linkables' => $this->linkableOptions(),
            'routes' => config('menus.routes', []),
            'maxDepth' => (int) config('menus.max_depth', 3),
        ];
    }

    /**
     * Menünün öğe ağacı, sıralı ve iç içe.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tree(Menu $menu): array
    {
        $items = $menu->items()
            ->with('linkable')
            ->get()
            ->groupBy(fn (MenuItem $item) => $item->parent_id ?? 0);

        return $this->buildNodes($items, 0);
    }

    public function createItem(Menu $menu, array $data): MenuItem
    {
        return $menu->items()->create($this->attributes($data) + ['parent_id' => null]);
    }

    public function updateItem(MenuItem $item, array $data): MenuItem
    {
        $item->update($this->attributes($data));

        return $item;
    }

    public function deleteItem(MenuItem $item): void
    {
        // Alt öğeler DB'de cascade ile silinir; kaç öğe gittiğini log'a yaz.
        $removed = 1 + $this->descendantCount($item);

        $item->delete();

        if ($removed > 1) {
            Activity::record(
                logName: 'menu',
                event: 'deleted',
                description: "Menü öğesi ve {$removed} alt öğesi silindi.",
                properties: ['old' => ['label' => $item->label, 'children' => $removed - 1]],
            );
        }
    }

    /**
     * Sürükle-bırak sonrası tüm ağacı tek istekte kaydeder.
     *
     * $nodes: [['id' => 4, 'children' => [['id' => 9, 'children' => []]]], ...]
     * Sıra dizideki index, derinlik iç içe geçmeden gelir. Query builder ile
     * yazılır (Eloquent olayı tetiklenmez), bu yüzden log tek özet kayıttır.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     */
    public function saveTree(Menu $menu, array $nodes): void
    {
        DB::transaction(function () use ($menu, $nodes) {
            $this->persistNodes($menu, $nodes, null);
        });

        // Query builder ->update() Eloquent olayı tetiklemez; önbelleği elle temizle.
        MenuRenderer::forget($menu->key);

        Activity::record(
            logName: 'menu',
            event: 'reorder',
            description: "\"{$menu->name}\" menüsünün sıralaması güncellendi.",
            subject: $menu,
        );
    }

    /**
     * "Öğe ekle" modalındaki kayıt seçenekleri, kaynak bazında gruplu.
     *
     * @return array<string, array{label: string, model: string, options: array<int, array{id: int, label: string}>}>
     */
    public function linkableOptions(): array
    {
        $result = [];

        foreach (config('menus.linkables', []) as $key => $config) {
            $result[$key] = [
                'label' => $config['label'],
                'model' => $config['model'],
                'options' => ($config['query'])()
                    ->get()
                    ->map(fn ($record) => [
                        'id' => $record->getKey(),
                        'label' => ($config['option_label'])($record),
                    ])
                    ->all(),
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, MenuItem>  $grouped  parent_id => öğeler
     * @return array<int, array<string, mixed>>
     */
    private function buildNodes(Collection $grouped, int $parentId): array
    {
        return $grouped->get($parentId, collect())
            ->map(fn (MenuItem $item) => $item->toPayload() + [
                'children' => $this->buildNodes($grouped, $item->id),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     */
    private function persistNodes(Menu $menu, array $nodes, ?int $parentId): void
    {
        foreach (array_values($nodes) as $index => $node) {
            // Yalnızca bu menüye ait öğeler taşınabilir — uydurma id sessizce atlanır.
            MenuItem::query()
                ->where('menu_id', $menu->id)
                ->whereKey($node['id'])
                ->update(['parent_id' => $parentId, 'sort_order' => $index]);

            if (! empty($node['children'])) {
                $this->persistNodes($menu, $node['children'], (int) $node['id']);
            }
        }
    }

    private function descendantCount(MenuItem $item): int
    {
        return $item->children->reduce(
            fn (int $carry, MenuItem $child) => $carry + 1 + $this->descendantCount($child),
            0,
        );
    }

    /** Formdan gelen ham veriyi bağlantı tipine göre temizler. */
    private function attributes(array $data): array
    {
        $type = $data['link_type'];

        return [
            'label' => $data['label'] ?? null,
            'link_type' => $type,
            'url' => $type === MenuItem::TYPE_URL ? ($data['url'] ?? null) : null,
            'route_name' => $type === MenuItem::TYPE_ROUTE ? ($data['route_name'] ?? null) : null,
            'linkable_type' => $type === MenuItem::TYPE_LINKABLE ? ($data['linkable_type'] ?? null) : null,
            'linkable_id' => $type === MenuItem::TYPE_LINKABLE ? ($data['linkable_id'] ?? null) : null,
            'target' => $data['target'] ?? '_self',
            'status' => $data['status'] ?? true,
        ];
    }
}
