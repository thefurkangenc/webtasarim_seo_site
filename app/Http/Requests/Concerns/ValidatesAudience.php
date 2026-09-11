<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesAudience
{
    /** @return array<string, array<int, mixed>> */
    protected function audienceRules(): array
    {
        return [
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'audience' => ['required', Rule::in(array_keys(config('notices.audiences')))],
            'page_ids' => ['nullable', 'array'],
            'page_ids.*' => ['integer', 'exists:pages,id'],
            'blog_ids' => ['nullable', 'array'],
            'blog_ids.*' => ['integer', 'exists:blogs,id'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ];
    }

    protected function withAudienceValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('audience') !== 'selected') {
                return;
            }

            if ($this->input('page_ids') || $this->input('blog_ids') || $this->input('service_ids')) {
                return;
            }

            $validator->errors()->add('audience', 'En az bir sayfa, yazı veya hizmet seçin.');
        });
    }
}
