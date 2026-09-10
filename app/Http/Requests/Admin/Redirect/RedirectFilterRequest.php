<?php

namespace App\Http\Requests\Admin\Redirect;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RedirectFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'match_type' => ['nullable', Rule::in(array_keys((array) config('redirects.match_types')))],
            'status' => ['nullable', Rule::in(['0', '1'])],
            'source' => ['nullable', Rule::in(['manual', 'auto', 'import'])],
            'sort' => ['nullable', Rule::in(['from_path', 'hits', 'last_hit_at', 'status_code', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
