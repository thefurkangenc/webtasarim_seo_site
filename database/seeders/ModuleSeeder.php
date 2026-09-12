<?php

namespace Database\Seeders;

use App\Models\Module\Module;
use Illuminate\Database\Seeder;

/**
 * config/modules.php'deki 13 modülü modules tablosuna upsert eder.
 * Tekrar çalıştırılabilir; mevcut name/is_active değerine dokunmaz.
 */
class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(config('modules.definitions', [])) as $key) {
            Module::query()->firstOrCreate(['key' => $key], ['is_active' => true]);
        }
    }
}
