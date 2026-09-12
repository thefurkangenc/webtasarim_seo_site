<?php

namespace App\Services\Module;

use App\Models\Media\MediaPreset;
use App\Models\Module\Module;
use App\Support\Activity;
use App\Support\MediaPresetRegistry;
use App\Support\ModuleRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleService
{
    public function __construct(
        private readonly ModuleRegistry $modules,
        private readonly MediaPresetRegistry $presets,
    ) {}

    /** @return array{modules: Collection, generalPresets: array} */
    public function formData(): array
    {
        $moduleKeys = $this->modules->all()->keys();

        // İKİNCİ parametre (preserveKeys) ŞART: groupBy varsayılan olarak her
        // grubun içindeki anahtarları da yeniden indeksler (0,1,2...) — preset
        // anahtarı ('blog.cover') kaybolur.
        $presetsByModule = $this->presets->all()->groupBy(
            fn (array $preset, string $key) => $moduleKeys->contains(explode('.', $key)[0]) ? explode('.', $key)[0] : '_general',
            true
        );

        $modules = $this->modules->all()->map(function (array $module) use ($presetsByModule) {
            $module['presets'] = $this->presetRows($presetsByModule->get($module['key'], collect()));

            return $module;
        });

        $generalPresets = $this->presetRows($presetsByModule->get('_general', collect()));

        return ['modules' => $modules, 'generalPresets' => $generalPresets];
    }

    /** @param  array<string, mixed>  $data */
    public function update(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data['modules'] ?? [] as $key => $attributes) {
                Module::query()->where('key', $key)->update([
                    'name' => ($attributes['name'] ?? null) ?: null,
                    'is_active' => (bool) ($attributes['is_active'] ?? false),
                ]);
            }

            foreach ($data['presets'] ?? [] as $field => $attributes) {
                $key = str_replace('__', '.', $field);

                MediaPreset::query()->where('key', $key)->update([
                    'width' => (int) $attributes['width'],
                    'height' => (int) $attributes['height'],
                ]);
            }
        });

        $this->modules->flush();
        $this->presets->flush();

        Activity::record('module', 'bulk_update', 'Modül Yönetimi ayarları güncellendi.');
    }

    /** @return list<array{key: string, field: string, width: int, height: int, label: string}> */
    private function presetRows(Collection $presets): array
    {
        return $presets
            ->map(fn (array $preset, string $key) => [
                'key' => $key,
                'field' => str_replace('.', '__', $key),
                'width' => $preset['width'],
                'height' => $preset['height'],
                'label' => $preset['label'],
            ])
            ->values()
            ->all();
    }
}
