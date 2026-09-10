<?php

namespace App\Http\Requests\Concerns;

/**
 * Paylaşılan form bileşenlerinin doğrulama kuralları.
 *
 * <x-admin::form.seo> ve <x-admin::form.tags> kullanan her FormRequest bu
 * trait'i ekleyip kurallarını kendi dizisine yayar:
 *
 *   return [...$this->seoRules(), ...$this->tagRules(), 'title' => [...]];
 */
trait ValidatesSharedFields
{
    /** @return array<string, array<int, string>> */
    protected function seoRules(string $prefix = 'seo'): array
    {
        return [
            $prefix => ['nullable', 'array'],
            "{$prefix}.meta_title" => ['nullable', 'string', 'max:255'],
            "{$prefix}.meta_description" => ['nullable', 'string', 'max:500'],
            "{$prefix}.meta_keywords" => ['nullable', 'string', 'max:500'],
            "{$prefix}.focus_keyword" => ['nullable', 'string', 'max:120'],
            "{$prefix}.og_media_id" => ['nullable', 'integer', 'exists:media,id'],
        ];
    }

    /**
     * <x-admin::form.schema> alanları. seoRules() ile aynı `seo` prefix'i
     * altında yaşar (seo tablosunda schema_type / schema_json / schema_override
     * kolonları var).
     *
     * @return array<string, array<int, mixed>>
     */
    protected function schemaRules(string $prefix = 'seo'): array
    {
        return [
            "{$prefix}.schema_type" => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z][A-Za-z0-9]*$/'],
            "{$prefix}.schema_override" => ['nullable', 'boolean'],
            "{$prefix}.schema_json" => [
                'nullable', 'string', 'max:20000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $decoded = json_decode((string) $value, true);

                    if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                        $fail('Geçerli bir JSON girin (bir nesne ya da nesne dizisi).');
                    }
                },
            ],
        ];
    }

    /** @return array<string, array<int, string>> */
    protected function tagRules(string $key = 'tags'): array
    {
        return [
            $key => ['nullable', 'array', 'max:20'],
            "{$key}.*" => ['string', 'max:50'],
        ];
    }
}
