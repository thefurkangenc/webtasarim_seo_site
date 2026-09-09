<?php

namespace App\Http\Requests\Admin\ServiceRegion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `parent_id` bilinçli olarak yok: bölge bulunduğu seviyede kalır, taşıma
 * desteklenmez. Yanlış yere eklenen bir bölge silinip yeniden açılır.
 */
class ServiceRegionUpdateRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150',
                Rule::unique('service_regions', 'slug')->ignore($this->route('region'))],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ];
    }
}
