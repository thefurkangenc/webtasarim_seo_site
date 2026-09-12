<?php

namespace App\Http\Requests\Admin\Service;

use App\Http\Requests\Concerns\FiltersPermissionedFields;
use App\Http\Requests\Concerns\ValidatesSharedFields;
use App\Models\Service\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceCreateRequest extends FormRequest
{
    use FiltersPermissionedFields, ValidatesSharedFields;

    public function authorize(): bool
    {
        return $this->user()->can('service.store');
    }

    /**
     * Paylaşılan bileşen alanları izinsiz kullanıcının validated() çıktısından
     * düşer (bkz. FiltersPermissionedFields). UI tarafı aynı izinlerle
     * `resources/views/admin/pages/service/form.blade.php`'de gizlenir.
     *
     * @return array<string, array<int, string>>
     */
    protected function permissionedFields(): array
    {
        return $this->sharedComponentPermissions('service', 'service_regions');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // Boş bırakılırsa başlıktan türetilir; verilirse benzersiz olmalı.
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Service::STATUSES))],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'service_regions' => ['nullable', 'array'],
            'service_regions.*' => ['integer', 'exists:service_regions,id'],

            'faqs' => ['nullable', 'array'],
            'faqs.*' => ['integer', 'exists:faqs,id'],

            ...$this->tagRules(),
            ...$this->seoRules(),
            ...$this->schemaRules(),
        ];
    }
}
