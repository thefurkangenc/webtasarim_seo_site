<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Yoast tarzı SEO skorlama alanları. Analiz kayıt kaydedilirken
| `HasSeo::syncSeo()` içinde yapılır, sonuç buraya yazılır; liste rozetleri ve
| "SEO Sağlığı" sayfası bu sütunları okur.
|
|   focus_keyword       Hedeflenen anahtar kelime/ifade.
|   seo_score           0-100 ağırlıklı skor.
|   readability_score   Ateşman okunabilirlik puanı (0-100'e kırpılmış).
|   score_checks        Tekil kontrol sonuçları (json) — sağlık sayfası detayı.
|   analyzed_at         Son analiz zamanı.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo', function (Blueprint $table) {
            $table->string('focus_keyword', 191)->nullable()->after('meta_keywords');
            $table->unsignedTinyInteger('seo_score')->nullable()->after('schema_override');
            $table->unsignedTinyInteger('readability_score')->nullable()->after('seo_score');
            $table->json('score_checks')->nullable()->after('readability_score');
            $table->timestamp('analyzed_at')->nullable()->after('score_checks');

            $table->index('seo_score');
        });
    }

    public function down(): void
    {
        Schema::table('seo', function (Blueprint $table) {
            $table->dropIndex(['seo_score']);
            $table->dropColumn(['focus_keyword', 'seo_score', 'readability_score', 'score_checks', 'analyzed_at']);
        });
    }
};
