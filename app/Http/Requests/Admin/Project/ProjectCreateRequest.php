<?php

namespace App\Http\Requests\Admin\Project;

use App\Http\Requests\Concerns\FiltersPermissionedFields;
use App\Http\Requests\Concerns\ValidatesSharedFields;
use App\Models\Project\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectCreateRequest extends FormRequest
{
    use FiltersPermissionedFields, ValidatesSharedFields;

    public function authorize(): bool
    {
        return $this->user()->can('project.store');
    }

    /**
     * Paylaşılan bileşen alanları izinsiz kullanıcının validated() çıktısından
     * düşer (bkz. FiltersPermissionedFields). UI tarafı aynı izinlerle
     * `resources/views/admin/pages/project/form.blade.php`'de gizlenir.
     *
     * @return array<string, array<int, string>>
     */
    protected function permissionedFields(): array
    {
        return $this->sharedComponentPermissions('project', 'project_category_id');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // Boş bırakılırsa başlıktan türetilir; verilirse benzersiz olmalı.
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($this->route('project'))],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'project_category_id' => ['nullable', 'integer', 'exists:project_categories,id'],
            'testimonial_id' => ['nullable', 'integer', 'exists:testimonials,id'],

            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'is_featured' => ['nullable', 'boolean'],

            // Künye
            'client_name' => ['nullable', 'string', 'max:150'],
            'sector' => ['nullable', 'string', 'max:100'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'duration' => ['nullable', 'string', 'max:60'],

            'technologies' => ['nullable', 'array', 'max:30'],
            'technologies.*' => ['string', 'max:60'],

            'results' => ['nullable', 'array', 'max:12'],
            'results.*.label' => ['nullable', 'string', 'max:80'],
            'results.*.value' => ['nullable', 'string', 'max:40'],
            'results.*.direction' => ['nullable', Rule::in(array_keys(Project::DIRECTIONS))],

            // Video: gömülü adres ya da kütüphaneden yüklenmiş dosya.
            'video_url' => ['nullable', 'url', 'max:255'],
            'video_media_id' => ['nullable', 'integer', 'exists:media,id'],

            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array', 'max:40'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],
            // Galeri bileşeni kapak işaretini ayrı bir alanda gönderir.
            'gallery_media_ids_cover' => ['nullable', 'integer', 'exists:media,id'],

            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],

            'faqs' => ['nullable', 'array'],
            'faqs.*' => ['integer', 'exists:faqs,id'],

            ...$this->tagRules(),
            ...$this->seoRules(),
            ...$this->schemaRules(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'project_url.url' => 'Yayındaki site adresini https:// ile başlayacak şekilde girin.',
            'completed_at.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
            'video_url.url' => 'Video adresini tam haliyle girin (örn. https://youtu.be/...).',
        ];
    }
}
