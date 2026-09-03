<?php

namespace App\Http\Requests\Admin\AiProvider;

/**
 * Kurallar ekleme ile aynı. Tek fark AiProviderCreateRequest::requiresKey()
 * içinde: düzenlemede route'ta bir sağlayıcı olduğu için API anahtarı boş
 * bırakılabilir ve kayıtlı anahtar korunur.
 */
class AiProviderUpdateRequest extends AiProviderCreateRequest {}
