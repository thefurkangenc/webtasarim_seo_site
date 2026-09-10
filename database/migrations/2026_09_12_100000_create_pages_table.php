<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // Üst sayfa silinince çocukları köke çıkar — PageService zaten
            // önce onları taşıyıp yollarını yeniden yazıyor, bu yalnızca
            // servis atlanırsa (elle sorgu) yetim kayıt kalmasın diye.
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug');

            // Çözülmüş tam yol: "kurumsal/hakkimizda". Ön yüz tek sorguda
            // bunu okur, üst sayfa değişince PageService alt ağacın tamamını
            // yeniden yazar. utf8mb4'te 400 karakter = 1600 bayt, InnoDB'nin
            // 3072 baytlık index sınırının altında.
            $table->string('path', 400)->unique();

            $table->string('excerpt', 500)->nullable();
            $table->longText('content')->nullable();

            // config/pages.php -> templates anahtarı.
            $table->string('template', 32)->default('default');
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
            $table->index(['status', 'path']);

            // Aynı üst sayfa altında iki kez aynı slug olamaz. Kök sayfalar
            // için parent_id NULL olduğundan MySQL bu kuralı uygulamaz —
            // onları yukarıdaki path unique'i korur (kökte path = slug).
            $table->unique(['parent_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
