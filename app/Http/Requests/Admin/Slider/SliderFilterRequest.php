<?php

namespace App\Http\Requests\Admin\Slider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SliderFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['title', 'sort_order', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,1000'], // sıralama modu tüm listeyi 1000 ile ister (core/table.js)
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
