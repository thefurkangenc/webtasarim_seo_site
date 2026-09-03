<?php

namespace App\Http\Requests\Admin\Ai;

use Illuminate\Foundation\Http\FormRequest;

class AiGenerateRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'ai_prompt_id' => ['required', 'integer', 'exists:ai_prompts,id'],
            // Şablondaki {{degisken}} yer tutucularını dolduran serbest alanlar.
            'input' => ['required', 'array'],
            'input.*' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
