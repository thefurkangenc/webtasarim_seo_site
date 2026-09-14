<?php

namespace Database\Seeders;

use App\Models\Country\Country;
use Illuminate\Database\Seeder;

/**
 * Telefon alanı için ülke sözlüğü. Şimdilik dört ülke; yenileri bu listeye
 * eklenir. Tekrar çalıştırılabilir — iso2 üzerinden günceller.
 */
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Türkiye', 'iso2' => 'TR', 'dial_code' => '90', 'mask' => '000 000 00 00', 'flag' => '🇹🇷', 'sort_order' => 1, 'strip_leading_zero' => true],
            ['name' => 'Amerika Birleşik Devletleri', 'iso2' => 'US', 'dial_code' => '1', 'mask' => '(000) 000-0000', 'flag' => '🇺🇸', 'sort_order' => 2, 'strip_leading_zero' => false],
            ['name' => 'Birleşik Krallık', 'iso2' => 'GB', 'dial_code' => '44', 'mask' => '0000 000000', 'flag' => '🇬🇧', 'sort_order' => 3, 'strip_leading_zero' => false],
            ['name' => 'Almanya', 'iso2' => 'DE', 'dial_code' => '49', 'mask' => '000 00000000', 'flag' => '🇩🇪', 'sort_order' => 4, 'strip_leading_zero' => false],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso2' => $country['iso2']],
                [...$country, 'is_active' => true],
            );
        }
    }
}
