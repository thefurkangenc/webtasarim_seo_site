<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Küçük içerik modüllerine (SSS, referans, yorum, neden biz) yayın anahtarı.
| Bu kayıtların "taslak" hali yoktu: silmeden gizlemenin yolu yoktu ve toplu
| işlemde yapılabilecek tek şey silmek olurdu. Mevcut kayıtlar aktiftir.
*/

return new class extends Migration
{
    private const TABLES = ['faqs', 'references', 'testimonials', 'why_choose_us'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
