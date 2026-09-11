<?php

namespace App\Http\Requests\Admin\Revision;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RevisionFilterRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'module' => ['nullable', Rule::in(array_keys(config('revisions.models')))],
            // Satır bazlı geçmiş: JS model sınıfının tam adını gönderir.
            'subject_type' => ['nullable', Rule::in(array_column(config('revisions.models'), 'class'))],
            'subject_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
