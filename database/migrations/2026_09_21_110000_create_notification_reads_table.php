<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        | Header'daki bildirim merkezinin "okundu" işaretleri.
        |
        | Bildirimlerin KENDİSİ saklanmaz — canlı durumdan türetilir (okunmamış
        | talep, kritik sistem kontrolü, kırık link…). Saklanması gereken tek
        | şey kullanıcının neyi gördüğüdür; aksi halde aynı bilgi iki yerde
        | tutulur ve biri bayatlar.
        |
        | `key` türetilmiş bildirimin kararlı kimliğidir ("lead:42",
        | "health:ssl"). Kullanıcı başına bir kez yazılır.
        */
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 191);
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
