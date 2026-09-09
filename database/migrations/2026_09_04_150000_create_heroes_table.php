<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    | Tanıtım alanı (hero) tekil bir kayıttır — tabloda tek satır tutulur,
    | HeroService::current() yoksa oluşturur. Görseller `mediables` pivotu
    | üzerinden 'gallery' koleksiyonunda saklanır, burada görsel kolonu yok.
    */
    public function up(): void
    {
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->string('badge')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('button_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};
