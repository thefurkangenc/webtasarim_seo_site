<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bölgeli hizmet adresleri iç içe geçtiği için (/hizmetler/web-tasarim/gaziantep/sahinbey)
 * kökten kayda kadarki slug zinciri gerekiyor. `path` adların zinciridir ve
 * insana gösterilir; `slug_path` onun adres karşılığıdır — ikisi de türetilmiş
 * alanlardır, ServiceRegionService kayıt sırasında birlikte yazar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_regions', function (Blueprint $table) {
            $table->string('slug_path')->nullable()->after('path')->index();
        });

        // Kökten aşağı doğru ilerleyerek mevcut ağacı doldur: her seviye bir
        // üstündeki zincire kendi slug'ını ekler.
        DB::table('service_regions')->whereNull('parent_id')
            ->update(['slug_path' => DB::raw('slug')]);

        while (DB::table('service_regions')->whereNull('slug_path')->exists()) {
            $filled = DB::table('service_regions as child')
                ->join('service_regions as parent', 'parent.id', '=', 'child.parent_id')
                ->whereNull('child.slug_path')
                ->whereNotNull('parent.slug_path')
                ->update(['child.slug_path' => DB::raw("CONCAT(parent.slug_path, '/', child.slug)")]);

            // Üstü olmayan ya da zinciri kopmuş kayıt kalmışsa sonsuz döngüye girme.
            if ($filled === 0) {
                break;
            }
        }
    }

    public function down(): void
    {
        Schema::table('service_regions', function (Blueprint $table) {
            $table->dropIndex(['slug_path']);
            $table->dropColumn('slug_path');
        });
    }
};
