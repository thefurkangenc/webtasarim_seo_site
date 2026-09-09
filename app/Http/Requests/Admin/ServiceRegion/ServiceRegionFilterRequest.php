<?php

namespace App\Http\Requests\Admin\ServiceRegion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRegionFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            // Kırılım seviyesi; boşsa iller listelenir. Arama varsa yok sayılır.
            'parent_id' => ['nullable', 'integer', 'exists:service_regions,id'],
            'is_active' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['name', 'sort_order', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
