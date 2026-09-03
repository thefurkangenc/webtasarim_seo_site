<?php

namespace App\Services\Ai;

use App\Jobs\Ai\GenerateContentJob;
use App\Models\Ai\AiGeneration;
use App\Models\Ai\AiPrompt;
use App\Models\Ai\AiProvider;
use App\Services\Ai\Contracts\ChatDriver;
use App\Services\Ai\Drivers\OllamaDriver;
use App\Services\Ai\Drivers\OpenAiCompatibleDriver;
use DomainException;
use Throwable;

/**
 * Yapay zeka üretiminin tek giriş noktası.
 *
 * Akış: dispatch() bir ai_generations kaydı açar ve işi kuyruğa atar;
 * kuyruk işçisi run() çağırır; arayüz kaydın durumunu sorgular.
 *
 * Blog dışındaki modüller de aynı yoldan geçer — tek fark prompt şablonunun
 * `key` değeridir (blog.content, service.content ...).
 */
class AiService
{
    private const DRIVERS = [
        'openai' => OpenAiCompatibleDriver::class,
        'deepseek' => OpenAiCompatibleDriver::class,
        'ollama' => OllamaDriver::class,
    ];

    /**
     * Üretimi kuyruğa alır ve takip kaydını döndürür.
     *
     * @param  array<string, mixed>  $input  Prompt değişkenleri
     */
    public function dispatch(AiPrompt $prompt, array $input): AiGeneration
    {
        $provider = $this->providerFor($prompt);

        $generation = AiGeneration::create([
            'ai_prompt_id' => $prompt->id,
            'ai_provider_id' => $provider->id,
            'user_id' => auth()->id(),
            'status' => AiGeneration::STATUS_QUEUED,
            'input' => $input,
        ]);

        GenerateContentJob::dispatch($generation->id, $provider->timeout);

        return $generation;
    }

    /**
     * Kuyruk işçisinin çağırdığı üretim. Hata durumunda istisna fırlatmaz;
     * sonucu kaydın kendisine yazar, arayüz oradan okur.
     */
    public function run(AiGeneration $generation): void
    {
        $generation->update(['status' => AiGeneration::STATUS_RUNNING]);

        $startedAt = microtime(true);

        try {
            $prompt = $generation->prompt;
            $provider = $generation->provider;

            if (! $prompt || ! $provider) {
                throw new DomainException('Üretimde kullanılan şablon ya da sağlayıcı silinmiş.');
            }

            $result = $this->driver($provider)->chat($provider, [
                ['role' => 'system', 'content' => $this->render($prompt->system_prompt, $generation->input ?? [])],
                ['role' => 'user', 'content' => $this->render($prompt->user_prompt, $generation->input ?? [])],
            ], ['json' => $provider->usesJsonMode()]);

            $generation->update([
                'status' => AiGeneration::STATUS_COMPLETED,
                'output' => $this->parse($result['content']),
                'tokens' => $result['tokens'],
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (Throwable $exception) {
            $generation->update([
                'status' => AiGeneration::STATUS_FAILED,
                'error' => $exception->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);
        }
    }

    /**
     * Sağlayıcıya kısa bir istek atar. Kaydetmeden önce anahtar ve model
     * doğru mu görmek için — uzun bir üretimi beklemeye gerek kalmasın.
     *
     * @return array{content: string, duration_ms: int}
     */
    public function test(AiProvider $provider): array
    {
        $startedAt = microtime(true);

        $result = $this->driver($provider)->chat(
            $provider,
            [['role' => 'user', 'content' => config('ai.test.prompt')]],
            ['timeout' => config('ai.test.timeout'), 'max_tokens' => config('ai.test.max_tokens')],
        );

        return [
            'content' => trim($result['content']),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    private function providerFor(AiPrompt $prompt): AiProvider
    {
        $provider = $prompt->provider?->is_active
            ? $prompt->provider
            : AiProvider::query()->where('is_active', true)->orderByDesc('is_default')->first();

        if (! $provider) {
            throw new DomainException(
                'Aktif bir yapay zeka sağlayıcısı yok. Önce Yapay Zeka → Sağlayıcılar ekranından bir sağlayıcı ekleyin.',
            );
        }

        return $provider;
    }

    private function driver(AiProvider $provider): ChatDriver
    {
        $class = self::DRIVERS[$provider->driver] ?? null;

        if (! $class) {
            throw new DomainException("Tanımsız sürücü: {$provider->driver}");
        }

        return app($class);
    }

    /** Şablondaki {{degisken}} yer tutucularını doldurur. */
    private function render(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = preg_replace(
                '/\{\{\s*'.preg_quote((string) $key, '/').'\s*\}\}/u',
                is_array($value) ? implode(', ', $value) : (string) $value,
                $template,
            );
        }

        // Doldurulmamış yer tutucular modele ham gitmemeli.
        return trim(preg_replace('/\{\{\s*[\w.]+\s*\}\}/u', '', $template));
    }

    /**
     * Model yanıtından JSON çıkarır.
     *
     * JSON modu açıkken bile bazı modeller cevabı ```json çitiyle sarar ya da
     * önüne açıklama yazar; bu yüzden ilk { ile son } arası alınır.
     *
     * @return array<string, mixed>
     */
    private function parse(string $content): array
    {
        $clean = trim(preg_replace('/^```(?:json)?|```$/mi', '', trim($content)));

        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');

        $decoded = $start !== false && $end !== false && $end > $start
            ? json_decode(substr($clean, $start, $end - $start + 1), true)
            : null;

        if (! is_array($decoded)) {
            throw new DomainException(
                'Model geçerli JSON döndürmedi. Ham yanıt: '.mb_substr(trim($content), 0, 500),
            );
        }

        return $decoded;
    }
}
