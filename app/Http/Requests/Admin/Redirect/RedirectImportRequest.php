<?php

namespace App\Http\Requests\Admin\Redirect;

use Illuminate\Foundation\Http\FormRequest;

class RedirectImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('redirect.import');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Bazı tarayıcılar CSV'yi text/plain gönderir; uzantı de kontrol edilir.
            'file' => ['required', 'file', 'max:2048', 'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['file' => 'dosya'];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['file.mimetypes' => 'Yalnızca CSV dosyası yükleyebilirsiniz.'];
    }
}
