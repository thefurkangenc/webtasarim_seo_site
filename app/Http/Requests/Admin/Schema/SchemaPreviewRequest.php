<?php

namespace App\Http\Requests\Admin\Schema;

use Illuminate\Foundation\Http\FormRequest;

class SchemaPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('schema.preview');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'url.required' => 'Bir sayfa seçin ya da adres girin.',
        ];
    }
}
