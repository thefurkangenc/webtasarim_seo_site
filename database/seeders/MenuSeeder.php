<?php

namespace Database\Seeders;

use App\Models\Menu\Menu;
use Illuminate\Database\Seeder;

/**
 * Menü konumlarını config/menus.php'den basar.
 *
 * Öğeler sihirbaz (veya panel) doldurur; burada varsayılan link basılmaz.
 * Tekrar çalıştırılabilir: konumlar `key` üzerinden updateOrCreate edilir,
 * dolu menünün öğelerine dokunulmaz.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('menus.locations') as $key => $meta) {
            Menu::updateOrCreate(
                ['key' => $key],
                ['name' => $meta['name'], 'title' => $meta['title']],
            );
        }
    }
}
