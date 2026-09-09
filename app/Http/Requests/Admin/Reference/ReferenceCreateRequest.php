<?php

namespace App\Http\Requests\Admin\Reference;

use Illuminate\Foundation\Http\FormRequest;

class ReferenceCreateRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'url' => ['nullable', 'url', 'max:255'],
            'logo_media_id' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'logo_media_id.required' => 'Firma logosu zorunludur.',
        ];
    }
}
