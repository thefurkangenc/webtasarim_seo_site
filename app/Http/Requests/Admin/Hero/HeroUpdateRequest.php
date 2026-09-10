<?php

namespace App\Http\Requests\Admin\Hero;

use Illuminate\Foundation\Http\FormRequest;

class HeroUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hero.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'badge' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'button_text' => ['nullable', 'string', 'max:100'],
            // Tam URL de göreli yol da (/iletisim) kabul edilir; 'url' kuralı yok.
            'button_url' => ['nullable', 'string', 'max:255'],
            'background_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],
            'gallery_media_ids_cover' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
