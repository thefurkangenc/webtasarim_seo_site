<?php

namespace App\Http\Requests\Admin\AiProvider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiProviderFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'driver' => ['nullable', Rule::in(array_keys(config('ai.drivers')))],
            'sort' => ['nullable', Rule::in(['name', 'driver', 'model', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
