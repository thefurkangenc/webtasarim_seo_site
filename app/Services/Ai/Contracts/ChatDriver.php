<?php

namespace App\Services\Ai\Contracts;

use App\Models\Ai\AiProvider;

/**
 * Bir yapay zeka sağlayıcısıyla sohbet protokolü.
 *
 * Sürücü yalnızca HTTP konuşur; prompt üretimi, JSON ayrıştırma ve kayıt
 * tutma AiService'in işidir. Yeni bir sağlayıcı eklemek = bu arayüzü
 * uygulayan bir sınıf + config/ai.php'ye bir satır.
 */
interface ChatDriver
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array{json?: bool, timeout?: int, max_tokens?: int}  $options
     * @return array{content: string, tokens: int|null}
     */
    public function chat(AiProvider $provider, array $messages, array $options = []): array;
}
