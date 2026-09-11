<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Kırık link denetimi sonuçları. Her satır "şu içerikteki şu adres çalışmıyor"
| demektir; tarama tekrarlandığında aynı satır güncellenir (yeni kayıt açılmaz),
| bu yüzden kaynak + adres birlikte benzersizdir.
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broken_links', function (Blueprint $table) {
            $table->id();

            // Linkin bulunduğu kayıt (Sayfa, Blog, Menü öğesi...).
            $table->string('source_type', 191);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_label');
            // İçeriğin hangi alanında bulundu (content, button_url...).
            $table->string('source_field', 60)->nullable();

            $table->string('url', 1000);
            // Adresin karşılaştırma/benzersizlik anahtarı: url uzun olduğu için
            // MySQL indeksine sığmıyor, hash'i tutuluyor.
            $table->char('url_hash', 40);

            $table->string('kind', 10)->default('link');      // link | image
            $table->string('scope', 10)->default('internal'); // internal | external
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('reason', 30);                     // not_found, timeout...
            $table->string('message', 500)->nullable();

            $table->boolean('ignored')->default(false)->index();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id', 'url_hash'], 'broken_links_source_url_unique');
            $table->index(['scope', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broken_links');
    }
};
