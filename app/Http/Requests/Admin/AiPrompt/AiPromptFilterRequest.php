<?php

namespace App\Http\Requests\Admin\AiPrompt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiPromptFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'key' => ['nullable', 'string', 'max:64'],
            'sort' => ['nullable', Rule::in(['name', 'key', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
