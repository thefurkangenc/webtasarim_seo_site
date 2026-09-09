<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'references' MySQL'de rezerve bir kelimedir; Laravel tablo adlarını
        // backtick ile sardığı için sorun çıkmaz — ham SQL yazmaktan kaçının.
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('url', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
