<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Schema.org (JSON-LD) kayıt bazında override alanları. Ayrı tablo açmıyoruz —
| her şema taşıyan model zaten `HasSeo` kullanıyor ve tek `seo` satırına sahip.
|
|   schema_type      Otomatik üretilen ana düğümün @type'ını ezer (örn. bir
|                    sayfayı WebPage yerine "Service" yapmak).
|   schema_json      Geçerli JSON — bir nesne ya da nesne dizisi; üretilen
|                    @graph'a olduğu gibi eklenir (ileri düzey tam kontrol).
|   schema_override  Açıksa bu sayfa için otomatik düğümler (WebPage, Service,
|                    BlogPosting, FAQPage, BreadcrumbList) üretilmez; yalnızca
|                    site geneli Organization + WebSite ve schema_json basılır.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo', function (Blueprint $table) {
            $table->string('schema_type', 40)->nullable()->after('robots_follow');
            $table->json('schema_json')->nullable()->after('schema_type');
            $table->boolean('schema_override')->default(false)->after('schema_json');
        });
    }

    public function down(): void
    {
        Schema::table('seo', function (Blueprint $table) {
            $table->dropColumn(['schema_type', 'schema_json', 'schema_override']);
        });
    }
};
