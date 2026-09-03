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
            "{$prefix}.canonical_url" => ['nullable', 'url', 'max:255'],
            "{$prefix}.robots_index" => ['boolean'],
            "{$prefix}.robots_follow" => ['boolean'],
            "{$prefix}.og_media_id" => ['nullable', 'integer', 'exists:media,id'],
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
