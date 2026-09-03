<?php

namespace App\Http\Requests\Admin\AiProvider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ekleme ve düzenleme aynı kuralları kullanır; tek fark API anahtarının
 * düzenlemede boş bırakılabilmesidir (boşsa kayıtlı anahtar korunur).
 */
class AiProviderRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'driver' => ['required', Rule::in(array_keys(config('ai.drivers')))],
            'base_url' => ['required', 'url', 'max:255'],
            'api_key' => [$this->requiresKey() ? 'required' : 'nullable', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:100'],
            'temperature' => ['required', 'numeric', 'between:0,2'],
            'max_tokens' => ['required', 'integer', 'between:100,32000'],
            'timeout' => ['required', 'integer', 'between:10,600'],
            'json_mode' => ['boolean'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ];
    }

    /** Ollama anahtar istemez; düzenlemede mevcut anahtar korunabilsin diye de gevşetilir. */
    private function requiresKey(): bool
    {
        return (config("ai.drivers.{$this->input('driver')}.requires_key") ?? false)
            && ! $this->route('provider');
    }
}
