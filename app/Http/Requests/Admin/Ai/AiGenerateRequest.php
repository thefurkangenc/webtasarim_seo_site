<?php

namespace App\Http\Requests\Admin\Ai;

use App\Models\Ai\AiPrompt;
use Illuminate\Foundation\Http\FormRequest;

class AiGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $id = $this->input('ai_prompt_id');

        if (! is_numeric($id)) {
            return true;
        }

        $prompt = AiPrompt::query()->find($id);

        return $prompt === null || $this->user()->can(AiPrompt::permissionForKey($prompt->key));
    }

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
