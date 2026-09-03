<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.required' => 'Dosya adı zorunludur.'];
    }
}
