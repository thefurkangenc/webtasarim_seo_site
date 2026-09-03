<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tek SEO tablosu, tüm modüller için. Yeni modüle SEO eklemek migration
        // gerektirmez — modele HasSeo trait'i eklemek yeter.
        Schema::create('seo', function (Blueprint $table) {
            $table->id();

            // morphs() kendi indeksini açar; burada unique zaten o işi gördüğü
            // için kolonlar elle tanımlanıyor, çift indeks oluşmasın.
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('canonical_url')->nullable();

            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);

            // Sosyal paylaşım görseli; medya kütüphanesinden gelir.
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();

            $table->timestamps();

            // Bir kayıt için tek SEO satırı.
            $table->unique(['seoable_type', 'seoable_id'], 'seo_seoable_unique');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['tag_id', 'taggable_type', 'taggable_id'], 'taggables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('seo');
    }
};
