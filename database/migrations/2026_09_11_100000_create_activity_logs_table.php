<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Denetim kaydı (audit log). Tek tablo, polimorfik iki uç:
|
|   subject_*  -> olayın konusu olan kayıt (Blog #12, Service #3 ...)
|   causer_*   -> olayı yapan (genelde User; ziyaretçi olaylarında boş)
|
| Kayıtlar DEĞİŞTİRİLMEZ: updated_at yok, uygulama hiçbir yerde update
| etmez (konum çözümlemesi hariç, o da yalnızca boş coğrafya kolonlarını
| doldurur). Ad/başlık gibi alanların anlık kopyaları (causer_name,
| subject_label) ayrıca saklanır — ilgili kayıt sonradan silinse bile log
| okunabilir kalsın diye.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // --- Olay ---------------------------------------------------
            // Modül anahtarı: 'blog', 'service', 'auth', 'media' ... Modül
            // bazlı filtreleme ve "Log Kayıtları" butonu bunu kullanır.
            $table->string('log_name', 64)->default('default');
            // created | updated | deleted | login | login_failed | forbidden ...
            $table->string('event', 40);
            $table->string('severity', 16)->default('info'); // info|notice|warning|critical
            $table->string('description', 500);

            // --- Konu (polimorfik) --------------------------------------
            $table->nullableMorphs('subject');
            // Kayıt silinse bile logda ne olduğu okunabilsin diye anlık kopya.
            $table->string('subject_label', 255)->nullable();

            // --- Fail (polimorfik) --------------------------------------
            $table->nullableMorphs('causer');
            $table->string('causer_name', 255)->nullable();
            $table->string('causer_email', 255)->nullable();
            // O anki roller — kullanıcının rolü sonradan değişse bile
            // işlemin hangi yetkiyle yapıldığı kaybolmasın.
            $table->json('causer_roles')->nullable();

            // --- Değişiklik ---------------------------------------------
            // {"old": {...}, "new": {...}} — hassas alanlar maskelenmiş.
            $table->json('properties')->nullable();
            // Hızlı gösterim/filtre için değişen alan adları.
            $table->json('changed_keys')->nullable();

            // --- İstek bağlamı ------------------------------------------
            $table->string('ip_address', 45)->nullable(); // IPv6 sığsın
            $table->text('user_agent')->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('browser_version', 32)->nullable();
            $table->string('platform', 64)->nullable();
            $table->string('platform_version', 32)->nullable();
            $table->string('device_type', 16)->nullable(); // desktop|mobile|tablet|bot
            $table->string('device_brand', 64)->nullable();
            $table->boolean('is_bot')->default(false);

            $table->string('method', 10)->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('route_name', 191)->nullable();
            $table->string('referer', 1000)->nullable();
            $table->string('locale', 16)->nullable();
            $table->string('session_id', 100)->nullable();
            // Tek bir HTTP isteğinde oluşan tüm loglar aynı değeri taşır —
            // toplu işlemlerde "bunlar birlikte oldu" bağı buradan kurulur.
            $table->uuid('request_id')->nullable();

            // --- Konum (kuyruktaki iş doldurur) -------------------------
            // pending: sırada | done | failed | skipped (özel/yerel IP)
            $table->string('geo_status', 12)->default('pending');
            $table->string('country_code', 2)->nullable();
            $table->string('country', 64)->nullable();
            $table->string('region', 64)->nullable();
            $table->string('city', 64)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('isp', 128)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Loglar değiştirilmez; updated_at taşımaz.
            $table->timestamp('created_at')->nullable()->index();

            $table->index(['log_name', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index('ip_address');
            $table->index('request_id');
            $table->index('geo_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
