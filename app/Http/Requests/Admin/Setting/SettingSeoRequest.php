<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:150'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'og_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
