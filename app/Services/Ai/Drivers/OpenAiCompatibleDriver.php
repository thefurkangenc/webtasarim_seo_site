<?php

namespace App\Services\Ai\Drivers;

use App\Models\Ai\AiProvider;
use App\Services\Ai\Contracts\ChatDriver;
use DomainException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI'nin /chat/completions sözleşmesini konuşan sağlayıcılar.
 * ChatGPT ve DeepSeek aynı gövdeyi kabul eder; fark yalnızca base_url ve model.
 */
class OpenAiCompatibleDriver implements ChatDriver
{
    public function chat(AiProvider $provider, array $messages, array $options = []): array
    {
        $body = [
            'model' => $provider->model,
            'messages' => $messages,
            'temperature' => $provider->temperature,
            'max_tokens' => $options['max_tokens'] ?? $provider->max_tokens,
        ];

        if ($options['json'] ?? false) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::timeout($options['timeout'] ?? $provider->timeout)
                ->withToken((string) $provider->api_key)
                ->acceptJson()
                ->post(rtrim($provider->base_url, '/').'/chat/completions', $body);
        } catch (ConnectionException $exception) {
            throw new DomainException("{$provider->name} adresine ulaşılamadı: {$exception->getMessage()}");
        }

        if ($response->failed()) {
            throw new DomainException(
                $response->json('error.message') ?? "{$provider->name} isteği reddetti (HTTP {$response->status()}).",
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new DomainException("{$provider->name} boş yanıt döndürdü.");
        }

        return [
            'content' => $content,
            'tokens' => $response->json('usage.total_tokens'),
        ];
    }
}
