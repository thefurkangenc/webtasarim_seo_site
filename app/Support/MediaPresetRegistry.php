<?php

namespace App\Support;

use App\Models\Media\MediaPreset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Medya kırpma preset'leri için TEK kaynak. DB boşsa (migration henüz
 * seed edilmemiş kurulum) config('media.presets')'e düşer — geriye dönük
 * güvenlik ağı.
 */
class MediaPresetRegistry
{
    public function get(string $key): ?array
    {
        return $this->state()[$key] ?? null;
    }

    /** @return Collection<string, array{width: int, height: int, label: string}> */
    public function all(): Collection
    {
        return collect($this->state());
    }

    public function flush(): void
    {
        Cache::forget('media.presets');
    }

    /**
     * Database cache sürücüsü nesne cache'lemeyi reddeder (bkz.
     * ModuleRegistry::state() yorumu) — Collection değil düz array cache'lenir.
     *
     * @return array<string, array{width: int, height: int, label: string}>
     */
    private function state(): array
    {
        return Cache::rememberForever('media.presets', function () {
            $rows = MediaPreset::query()->get(['key', 'width', 'height', 'label']);

            if ($rows->isEmpty()) {
                return config('media.presets', []);
            }

            return $rows->mapWithKeys(fn (MediaPreset $preset) => [
                $preset->key => ['width' => $preset->width, 'height' => $preset->height, 'label' => $preset->label],
            ])->all();
        });
    }
}
