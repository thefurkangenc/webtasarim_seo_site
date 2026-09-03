<?php

/*
| Yapay zeka sürücülerinin sabit bilgileri. Sağlayıcı kayıtları (anahtar,
| model, sıcaklık) panelden yönetilir; burada yalnızca sürücünün nasıl
| konuştuğu ve form için makul varsayılanlar durur.
*/

return [

    'drivers' => [

        'openai' => [
            'label' => 'ChatGPT (OpenAI)',
            'base_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-4o-mini',
            'requires_key' => true,
            // Modelden geçerli JSON dönmesini garantileyen response_format desteği.
            'supports_json' => true,
        ],

        'deepseek' => [
            'label' => 'DeepSeek',
            'base_url' => 'https://api.deepseek.com/v1',
            'model' => 'deepseek-chat',
            'requires_key' => true,
            'supports_json' => true,
        ],

        'ollama' => [
            'label' => 'Ollama (yerel)',
            'base_url' => 'http://localhost:11434',
            'model' => 'llama3.1',
            'requires_key' => false,
            'supports_json' => true,
        ],

    ],

    /*
    | Bağlantı testinde kullanılan kısa istek. Uzun bir üretim beklemeden
    | anahtar ve model doğru mu görmek için.
    */
    'test' => [
        'prompt' => 'Yalnızca "tamam" yaz.',
        'timeout' => 30,
        'max_tokens' => 16,
    ],

];
