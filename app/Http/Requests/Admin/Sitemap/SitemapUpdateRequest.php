<?php

namespace App\Http\Requests\Admin\Sitemap;

use Illuminate\Foundation\Http\FormRequest;

class SitemapUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sitemap.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // Kaynak başına bir switch: source_pages, source_blog ...
        $rules = collect(array_keys(config('sitemap.sources')))
            ->mapWithKeys(fn (string $key) => ["source_{$key}" => ['nullable', 'boolean']])
            ->all();

        return $rules + [
            'excluded_urls' => ['nullable', 'string', 'max:10000'],
            'extra_urls' => ['nullable', 'string', 'max:10000'],
            'robots_txt' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
