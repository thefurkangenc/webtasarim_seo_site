<?php

namespace App\Http\Requests\Admin\AiPrompt;

use Illuminate\Foundation\Http\FormRequest;

class AiPromptRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9]+(\.[a-z0-9-]+)*$/'],
            'system_prompt' => ['required', 'string', 'max:8000'],
            'user_prompt' => ['required', 'string', 'max:8000'],
            'ai_provider_id' => ['nullable', 'integer', 'exists:ai_providers,id'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'key.regex' => 'Anahtar yalnızca küçük harf, rakam ve nokta içerebilir (örn. blog.content).',
        ];
    }
}
