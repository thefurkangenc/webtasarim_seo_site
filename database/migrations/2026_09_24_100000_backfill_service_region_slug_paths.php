<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `slug_path` kolonu eklendikten sonra ServiceRegionSeeder illeri bu alanı
 * yazmadan yeniden kaydediyordu; kurulum ya da `db:seed` sonrası tüm bölgeler
 * NULL kaldı. Sonuç: bölgeli hizmet adresleri 404 veriyor, kenar çubuğu tüm
 * bölgeleri tek bir boş grupta topluyordu. Seeder düzeltildi, bu migration
 * boş kalan kayıtları kökten aşağı doğru doldurur.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_regions')->whereNull('parent_id')->whereNull('slug_path')
            ->update(['slug_path' => DB::raw('slug')]);

        while (DB::table('service_regions')->whereNull('slug_path')->exists()) {
            $filled = DB::table('service_regions as child')
                ->join('service_regions as parent', 'parent.id', '=', 'child.parent_id')
                ->whereNull('child.slug_path')
                ->whereNotNull('parent.slug_path')
                ->update(['child.slug_path' => DB::raw("CONCAT(parent.slug_path, '/', child.slug)")]);

            if ($filled === 0) {
                break;
            }
        }
    }

    public function down(): void
    {
        //
    }
};
