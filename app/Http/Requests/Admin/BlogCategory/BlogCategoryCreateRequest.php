<?php

namespace App\Http\Requests\Admin\BlogCategory;

use App\Http\Requests\Concerns\ValidatesSharedFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogCategoryCreateRequest extends FormRequest
{
    use ValidatesSharedFields;

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150',
                Rule::unique('blog_categories', 'slug')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
            'is_active' => ['boolean'],

            ...$this->seoRules(),
        ];
    }
}
