<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            // Kaynak adres, normalize edilmiş: baş/son eğik çizgi yok, küçük
            // harf, sorgu dizesi yok. 191 = utf8mb4'te güvenli index sınırı;
            // yönlendirme kaynakları pratikte kısa olur, doğrulama da sınırlar.
            $table->string('from_path', 191);

            // exact  = birebir eşleşme
            // prefix = /eski-blog ve altındaki her şey → hedef + kalan yol
            // regex  = kaynak bir desen, hedefte $1..$9 kullanılabilir
            $table->string('match_type', 10)->default('exact');

            // Hedef: iç yol (/yeni-sayfa) ya da tam URL (https://...).
            $table->string('to_url', 2000)->nullable();

            // 301 kalıcı, 302 geçici, 307 geçici (metot korur), 410 kalkmış (Location yok).
            $table->unsignedSmallInteger('status_code')->default(301);

            $table->boolean('is_active')->default(true);

            // manual = panelden, auto = slug değişiminden, import = CSV.
            $table->string('source', 10)->default('manual');
            $table->string('notes', 500)->nullable();

            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();

            $table->timestamps();

            // Tam eşleşme araması bu index'ten döner. Aynı kaynağı iki kez
            // tanımlamak anlamsız — hangi kural kazanacağı belirsiz olur.
            $table->unique('from_path');
            $table->index(['is_active', 'match_type']);
        });

        Schema::create('not_found_logs', function (Blueprint $table) {
            $table->id();

            // 404 alan yol, sorgu dizesi olmadan. Aynı yol tekrar tekrar 404
            // alırsa tek satırda sayaç artar.
            $table->string('path', 191)->unique();

            $table->unsignedBigInteger('hits')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->string('last_referer', 2000)->nullable();
            $table->string('last_user_agent', 500)->nullable();
            $table->string('last_ip', 45)->nullable();

            // Bu yol için bir yönlendirme oluşturulunca true — listede gizlenir.
            $table->boolean('resolved')->default(false);

            $table->timestamps();

            $table->index(['resolved', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('not_found_logs');
        Schema::dropIfExists('redirects');
    }
};
