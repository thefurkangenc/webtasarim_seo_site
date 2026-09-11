<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| İçerik revizyonları. Her satır bir kaydın DEĞİŞTİRİLMEDEN ÖNCEKİ tam
| halidir (alanlar + SEO + etiket + medya + SSS bağları); geri yükleme bu
| anlık görüntüden yapılır.
|
| Denetim kaydıyla (activity_logs) karıştırılmamalı: log "ne değişti"yi
| yazar, revizyon "eski hali neydi"yi saklar. İkisi ayrı ömür yaşar —
| revizyonlar kayıt başına son N ile sınırlıdır (config/revisions.php).
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();

            $table->string('revisionable_type', 191);
            $table->unsignedBigInteger('revisionable_id');

            // Değişikliği yapan (yani bu sürümü geride bırakan) kullanıcı.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Kaydın o andaki tam hali.
            $table->longText('snapshot');
            // Bu sürümden sonra hangi alanların değiştiği — listede rozet olur.
            $table->json('changed_keys')->nullable();
            // Aynı içeriğin peş peşe iki kez yazılmasını engelleyen imza.
            $table->char('hash', 40);
            // Kaydın o anki başlığı: kayıt silinse bile listede okunabilsin.
            $table->string('label')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['revisionable_type', 'revisionable_id', 'id'], 'revisions_subject_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
