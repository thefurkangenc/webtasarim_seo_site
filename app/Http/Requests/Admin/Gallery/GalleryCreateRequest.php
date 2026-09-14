<?php

namespace App\Http\Requests\Admin\Gallery;

use App\Http\Requests\Concerns\FiltersPermissionedFields;
use App\Http\Requests\Concerns\ValidatesSharedFields;
use App\Models\Gallery\Gallery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GalleryCreateRequest extends FormRequest
{
    use FiltersPermissionedFields, ValidatesSharedFields;

    public function authorize(): bool
    {
        return $this->user()->can('gallery.store');
    }

    /**
     * SEO ve Schema.org izinsiz kullanıcının validated() çıktısından düşer.
     * UI tarafı aynı izinlerle form Blade'inde gizlenir.
     *
     * @return array<string, array<int, string>>
     */
    protected function permissionedFields(): array
    {
        return $this->sharedComponentPermissions('gallery', null);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('galleries', 'slug')->ignore($this->route('gallery'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(Gallery::STATUSES))],
            'gallery_media_ids' => ['nullable', 'array', 'max:40'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],
            'gallery_media_ids_cover' => ['nullable', 'integer', 'exists:media,id'],
            ...$this->seoRules(),
            ...$this->schemaRules(),
        ];
    }
}
