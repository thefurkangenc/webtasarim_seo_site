<?php

namespace App\Support;

use App\Models\Module\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Modüllerin aktif/pasif durumu ve görünen adı için TEK kaynak.
 *
 * DB'de satırı olmayan bir modül (henüz seed edilmemiş, ya da bilinmeyen
 * bir anahtar) varsayılan olarak AKTİF sayılır — "fail open": eksik veri
 * bir modülü yanlışlıkla kapatmasın.
 */
class ModuleRegistry
{
    public function isActive(string $key): bool
    {
        return $this->state()[$key]['is_active'] ?? true;
    }

    public function label(string $key): string
    {
        $name = $this->state()[$key]['name'] ?? null;

        return filled($name) ? $name : config("modules.definitions.{$key}.label", $key);
    }

    /** @return Collection<string, array{key: string, label: string, icon: string, description: string, name: ?string, is_active: bool}> */
    public function all(): Collection
    {
        $state = $this->state();

        return collect(config('modules.definitions', []))->map(fn (array $definition, string $key) => [
            'key' => $key,
            'label' => $definition['label'],
            'icon' => $definition['icon'],
            'description' => $definition['description'],
            'name' => $state[$key]['name'] ?? null,
            'is_active' => $state[$key]['is_active'] ?? true,
        ]);
    }

    public function flush(): void
    {
        Cache::forget('modules.state');
    }

    /**
     * Database cache sürücüsü nesne cache'lemeyi reddeder (`serialize` => false
     * ise `allowed_classes => false` ile unserialize eder, her nesne
     * __PHP_Incomplete_Class'a döner) — bu yüzden Collection değil düz array
     * cache'lenir. Aynı kural SettingService::getGroup()'ta da uygulanıyor.
     *
     * @return array<string, array{name: ?string, is_active: bool}>
     */
    private function state(): array
    {
        return Cache::rememberForever('modules.state', fn () => Module::query()
            ->get(['key', 'name', 'is_active'])
            ->mapWithKeys(fn (Module $module) => [
                $module->key => ['name' => $module->name, 'is_active' => $module->is_active],
            ])
            ->all());
    }
}
