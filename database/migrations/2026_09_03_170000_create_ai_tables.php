<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // openai | deepseek | ollama — tanımları config/ai.php'de.
            $table->string('driver', 32);
            $table->string('base_url');
            // encrypted cast; veritabanında düz metin durmaz.
            $table->text('api_key')->nullable();
            $table->string('model');

            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->unsignedInteger('max_tokens')->default(4000);
            $table->unsignedSmallInteger('timeout')->default(180);

            // Sürücüye özel bayraklar: { "json_mode": true }
            $table->json('options')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Şablonu kullanan yer: blog.content, service.content ...
            $table->string('key', 64);
            $table->text('system_prompt');
            $table->text('user_prompt');
            // Boşsa varsayılan sağlayıcı kullanılır.
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['key', 'is_active']);
        });

        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_prompt_id')->nullable()->constrained('ai_prompts')->nullOnDelete();
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // queued | running | completed | failed
            $table->string('status', 16)->default('queued');
            // Kullanıcının verdiği değişkenler: { "keywords": "...", "title": "..." }
            $table->json('input')->nullable();
            // Ayrıştırılmış model çıktısı.
            $table->json('output')->nullable();
            $table->text('error')->nullable();

            $table->unsignedInteger('tokens')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('ai_providers');
    }
};
