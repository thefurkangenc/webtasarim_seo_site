<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_category_id')->nullable()->constrained()->nullOnDelete();
            // Yazar; kullanıcı silinirse proje kalır, yazar boşalır.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Projede gösterilecek müşteri yorumu — yorum silinirse proje kalır.
            $table->foreignId('testimonial_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();

            // Künye: ön yüzde proje sayfasının sağ kolonunda duran bilgi kutusu.
            $table->string('client_name')->nullable();
            $table->string('sector')->nullable();
            $table->string('project_url')->nullable();
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('duration')->nullable();

            /*
            | Teknoloji listesi ve sonuç blokları JSON tutulur: ikisi de yalnızca
            | proje kaydıyla birlikte okunur, kendi başlarına sorgulanmaz ve
            | sıralaması kullanıcının verdiği sıradır. Ayrı tablo açmak bir model,
            | bir servis ve bir sıralama uç noktası daha doğururdu; JSON olarak
            | revizyon geçmişine de kendiliğinden girer (alan diff'i olarak).
            */
            $table->json('technologies')->nullable();
            $table->json('results')->nullable();

            // Gömülü video adresi (YouTube/Vimeo). Kendi sunucumuzdaki mp4 ise
            // medya kütüphanesine yüklenir ve 'video' koleksiyonuna bağlanır.
            $table->string('video_url')->nullable();

            // draft | published
            $table->string('status', 16)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index(['project_category_id', 'status']);
        });

        // Proje ↔ hizmet: hizmet sayfasında "bu hizmette yaptığımız işler"
        // bloğunu kurabilmek için. Tablo adı açıkça verildi.
        Schema::create('project_service', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->primary(['project_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_service');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_categories');
    }
};
