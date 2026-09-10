<?php

namespace Database\Seeders;

use App\Models\Menu\Menu;
use App\Models\Menu\MenuItem;
use App\Models\Service\Service;
use Illuminate\Database\Seeder;

/**
 * Menü konumlarını config/menus.php'den basar ve ilk kurulumda header/footer'a
 * makul bir başlangıç menüsü koyar.
 *
 * Tekrar çalıştırılabilir: konumlar `key` üzerinden updateOrCreate edilir,
 * öğeler yalnızca menü tamamen boşsa eklenir (kullanıcının düzenlediği menüyü
 * ezmez).
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('menus.locations') as $key => $meta) {
            $menu = Menu::updateOrCreate(
                ['key' => $key],
                ['name' => $meta['name'], 'title' => $meta['title']],
            );

            if ($menu->items()->exists()) {
                continue;
            }

            $this->seedItems($menu, $key);
        }
    }

    private function seedItems(Menu $menu, string $key): void
    {
        $items = match ($key) {
            'header' => [
                ['label' => 'Ana Sayfa', 'route_name' => 'anasayfa'],
                ['label' => 'Hakkımızda', 'route_name' => 'hakkimizda'],
                ['label' => 'Hizmetler', 'route_name' => 'hizmetler'],
                ['label' => 'Blog', 'route_name' => 'blog'],
                ['label' => 'İletişim', 'route_name' => 'iletisim'],
            ],
            'footer_primary' => [
                ['label' => 'Ana Sayfa', 'route_name' => 'anasayfa'],
                ['label' => 'Hakkımızda', 'route_name' => 'hakkimizda'],
                ['label' => 'Hizmetler', 'route_name' => 'hizmetler'],
                ['label' => 'Blog', 'route_name' => 'blog'],
                ['label' => 'İletişim', 'route_name' => 'iletisim'],
                ['label' => 'KVKK', 'route_name' => 'kvkk'],
                ['label' => 'Çerez Politikası', 'route_name' => 'cerez-politikasi'],
            ],
            // 2. footer sütunu (Hizmetlerimiz) yayındaki hizmetlere bağlanır;
            // hiç hizmet yoksa boş kalır, panelden doldurulur.
            'footer_secondary' => Service::query()
                ->where('status', Service::STATUS_PUBLISHED)
                ->orderBy('sort_order')
                ->limit(6)
                ->get()
                ->map(fn ($service) => [
                    'link_type' => MenuItem::TYPE_LINKABLE,
                    'linkable_type' => Service::class,
                    'linkable_id' => $service->id,
                    'label' => null,
                ])
                ->all(),
            default => [],
        };

        foreach ($items as $index => $item) {
            $menu->items()->create([
                'label' => $item['label'] ?? null,
                'link_type' => $item['link_type'] ?? MenuItem::TYPE_ROUTE,
                'route_name' => $item['route_name'] ?? null,
                'linkable_type' => $item['linkable_type'] ?? null,
                'linkable_id' => $item['linkable_id'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
