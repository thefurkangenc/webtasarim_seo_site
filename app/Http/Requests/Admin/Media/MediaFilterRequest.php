<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.datatable');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer'],
            'type' => ['nullable', Rule::in(['image', 'other'])],
            'unattached' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['created_at', 'name', 'size'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
