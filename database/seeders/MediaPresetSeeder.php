<?php

namespace Database\Seeders;

use App\Models\Media\MediaPreset;
use Illuminate\Database\Seeder;

/**
 * config/media.php > presets'teki her anahtarı media_presets tablosuna
 * kopyalar. Tekrar çalıştırılabilir; DB'de zaten var olan satırın
 * width/height/label'ına dokunmaz (panelden değiştirilmiş olabilir).
 */
class MediaPresetSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('media.presets', []) as $key => $preset) {
            MediaPreset::query()->firstOrCreate(['key' => $key], [
                'width' => $preset['width'],
                'height' => $preset['height'],
                'label' => $preset['label'],
            ]);
        }
    }
}
