<?php

namespace App\Http\Requests\Admin\Blog;

use App\Http\Requests\Concerns\ValidatesSharedFields;
use App\Models\Blog\Blog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogCreateRequest extends FormRequest
{
    use ValidatesSharedFields;

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // Boş bırakılırsa başlıktan türetilir; verilirse benzersiz olmalı.
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($this->route('blog'))],
            'blog_category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Blog::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],

            'faqs' => ['nullable', 'array'],
            'faqs.*' => ['integer', 'exists:faqs,id'],

            ...$this->tagRules(),
            ...$this->seoRules(),
            ...$this->schemaRules(),
        ];
    }
}
