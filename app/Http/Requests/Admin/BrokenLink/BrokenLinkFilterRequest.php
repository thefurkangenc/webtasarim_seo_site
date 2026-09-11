<?php

namespace App\Http\Requests\Admin\BrokenLink;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrokenLinkFilterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Checkbox "1"/"0" ya da "true"/"false" gelebilir; bool'a sabitle.
        if ($this->has('include_ignored')) {
            $this->merge(['include_ignored' => filter_var($this->input('include_ignored'), FILTER_VALIDATE_BOOL)]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:191'],
            'scope' => ['nullable', Rule::in(array_keys((array) config('broken-links.scopes')))],
            'kind' => ['nullable', Rule::in(array_keys((array) config('broken-links.kinds')))],
            'reason' => ['nullable', Rule::in(array_keys((array) config('broken-links.reasons')))],
            'source_type' => ['nullable', Rule::in(array_keys((array) config('broken-links.sources')))],
            'include_ignored' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['url', 'source_label', 'reason', 'scope', 'first_seen_at', 'last_checked_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
