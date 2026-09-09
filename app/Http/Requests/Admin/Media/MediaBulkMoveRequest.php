<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MediaBulkMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.bulk-move');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'media' => ['sometimes', 'array'],
            'media.*' => ['integer', 'exists:media,id'],
            'folders' => ['sometimes', 'array'],
            'folders.*' => ['integer', 'exists:media_folders,id'],
            'target_folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('media', []) === [] && $this->input('folders', []) === []) {
                $validator->errors()->add('media', 'Taşınacak en az bir öğe seçilmeli.');
            }
        });
    }
}
