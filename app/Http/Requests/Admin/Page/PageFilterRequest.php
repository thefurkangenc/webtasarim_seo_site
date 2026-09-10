<?php

namespace App\Http\Requests\Admin\Page;

use App\Models\Page\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Page::STATUSES))],
            'template' => ['nullable', Rule::in(array_keys((array) config('pages.templates')))],
            // 0 = yalnızca kök sayfalar; bir kimlik = o sayfanın alt sayfaları.
            'parent_id' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(['title', 'path', 'status', 'template', 'sort_order', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
