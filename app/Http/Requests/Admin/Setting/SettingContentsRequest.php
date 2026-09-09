<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingContentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.contents.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('about_title') && is_string($this->input('about_title'))) {
            $this->merge(['about_title' => trim($this->input('about_title'))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_content' => ['nullable', 'string'],
            'cookie_content' => ['nullable', 'string'],
            'kvkk_content' => ['nullable', 'string'],
        ];
    }
}
