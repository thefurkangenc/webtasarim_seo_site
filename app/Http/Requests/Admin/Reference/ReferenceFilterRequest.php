<?php

namespace App\Http\Requests\Admin\Reference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['name', 'sort_order', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,1000'], // sıralama modu tüm listeyi 1000 ile ister (core/table.js)
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
