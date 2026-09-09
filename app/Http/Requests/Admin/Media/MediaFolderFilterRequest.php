<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaFolderFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.folders.index');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ];
    }
}
