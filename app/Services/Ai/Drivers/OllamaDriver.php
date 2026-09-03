<?php

namespace App\Services\Ai\Drivers;

use App\Models\Ai\AiProvider;
use App\Services\Ai\Contracts\ChatDriver;
use DomainException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Ollama'nın kendi /api/chat uç noktası.
 *
 * OpenAI uyumlu katmanı yerine yerel API kullanılıyor: her kurulumda açık
 * olduğu garanti, API anahtarı istemiyor ve model listesi aynı adresten okunuyor.
 */
class OllamaDriver implements ChatDriver
{
    public function chat(AiProvider $provider, array $messages, array $options = []): array
    {
        $body = [
            'model' => $provider->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $provider->temperature,
                'num_predict' => $options['max_tokens'] ?? $provider->max_tokens,
            ],
        ];

        if ($options['json'] ?? false) {
            $body['format'] = 'json';
        }

        try {
            $response = Http::timeout($options['timeout'] ?? $provider->timeout)
                ->acceptJson()
                ->post(rtrim($provider->base_url, '/').'/api/chat', $body);
        } catch (ConnectionException $exception) {
            throw new DomainException(
                "Ollama'ya ulaşılamadı ({$provider->base_url}). Servis çalışıyor mu? — {$exception->getMessage()}",
            );
        }

        if ($response->failed()) {
            throw new DomainException(
                $response->json('error') ?? "Ollama isteği reddetti (HTTP {$response->status()}).",
            );
        }

        $content = $response->json('message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new DomainException('Ollama boş yanıt döndürdü.');
        }

        return [
            'content' => $content,
            // Ollama toplam vermez; istem ve yanıt sayaçlarını topluyoruz.
            'tokens' => ($response->json('prompt_eval_count') ?? 0) + ($response->json('eval_count') ?? 0) ?: null,
        ];
    }
}
