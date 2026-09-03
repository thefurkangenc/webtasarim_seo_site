<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();

            $table->string('disk')->default('public');
            // Kullanıma hazır dosya (kırpılmış + preset boyutunda + webp).
            $table->string('path');
            // Ham yükleme; yalnızca işlenmiş görsellerde dolu, yeniden kırpma için saklanır.
            $table->string('original_path')->nullable();

            $table->string('name');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('extension', 16);
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->string('alt')->nullable();
            $table->string('title')->nullable();

            // Cropper.js çıktısı: x, y, width, height, rotate, scaleX, scaleY
            $table->json('crop')->nullable();
            // Türetilmiş boyutlar: { "thumb": "uploads/...", "medium": "uploads/..." }
            $table->json('conversions')->nullable();
            $table->string('preset')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['folder_id', 'created_at']);
            $table->index('mime_type');
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->morphs('mediable');
            $table->string('collection')->default('default');
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['media_id', 'mediable_type', 'mediable_id', 'collection'], 'mediables_unique');
            $table->index(['mediable_type', 'mediable_id', 'collection'], 'mediables_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_folders');
    }
};
