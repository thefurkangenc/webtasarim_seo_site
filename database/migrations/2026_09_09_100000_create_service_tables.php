<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sonsuz derinlikli bölge ağacı: parent_id boşsa şehir, doluysa alt bölge.
        // Şehirlerin id'si plaka kodudur (seeder sabit id ile basar).
        Schema::create('service_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('service_regions')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            // "Gaziantep Şahinbey" — kökten bu kayda kadarki insan okunur yol.
            // Yalnızca liste/arama görünümü için; yer tutucu çözümü parent
            // zinciri üzerinden yapılır (bölge adında tire geçerse bozulmasın).
            $table->string('path')->nullable();
            // 0 = şehir, 1 = ilçe, 2 = mahalle...
            $table->unsignedTinyInteger('depth')->default(0);
            // Bölgeye özel metin — aynı hizmetin 81 sayfası birbirinin kopyası
            // olmasın diye her bölge sayfasına eklenen özgün blok.
            $table->text('description')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
            $table->index(['is_active', 'depth']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            // Yazar; kullanıcı silinirse hizmet kalır, yazar boşalır.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();

            // draft | published
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        // Bir hizmet çok bölgeye bağlanır; her (hizmet, bölge) çifti ön yüzde
        // bir SEO sayfası üretir. Tablo adı açıkça verildi — Eloquent'in
        // türeteceği 'service_service_region' okunmaz.
        Schema::create('service_region_service', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_region_id')->constrained()->cascadeOnDelete();

            $table->primary(['service_id', 'service_region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_region_service');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_regions');
    }
};
