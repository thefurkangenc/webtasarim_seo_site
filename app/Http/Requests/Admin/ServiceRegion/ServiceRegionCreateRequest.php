<?php

namespace App\Http\Requests\Admin\ServiceRegion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRegionCreateRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Kırılımda bulunulan seviyeden gelir; boşsa yeni bir il açılıyor.
            'parent_id' => ['nullable', 'integer', 'exists:service_regions,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('service_regions', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ];
    }
}
