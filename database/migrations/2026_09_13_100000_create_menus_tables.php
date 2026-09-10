<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menü konumları: sabit yuvalar (header, footer sütunları). Panelden
        // menü eklenip silinmez — yalnızca içindeki öğeler yönetilir. Yuvalar
        // config/menus.php'den MenuSeeder ile basılır.
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name');
            // Ön yüzde görünen başlık — footer sütunlarında <h4>, header'da yok.
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();

            // Kayda bağlı öğede boş bırakılabilir — o zaman kaydın adı kullanılır.
            $table->string('label')->nullable();

            // url      = elle girilen adres (/hakkimizda, https://...)
            // route    = parametresiz adlandırılmış route (config/menus.php > routes)
            // linkable = bir kayda bağlı (Page / Service / Blog) — URL render anında çözülür
            $table->string('link_type', 16)->default('url');
            $table->string('url', 2000)->nullable();
            $table->string('route_name', 100)->nullable();
            $table->nullableMorphs('linkable');

            $table->string('target', 10)->default('_self');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
