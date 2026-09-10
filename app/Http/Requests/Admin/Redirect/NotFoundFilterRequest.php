<?php

namespace App\Http\Requests\Admin\Redirect;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotFoundFilterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Checkbox "1"/"0" ya da "true"/"false" gelebilir; bool'a sabitle.
        if ($this->has('include_resolved')) {
            $this->merge(['include_resolved' => filter_var($this->input('include_resolved'), FILTER_VALIDATE_BOOL)]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'include_resolved' => ['boolean'],
            'sort' => ['nullable', Rule::in(['path', 'hits', 'last_seen_at', 'first_seen_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
