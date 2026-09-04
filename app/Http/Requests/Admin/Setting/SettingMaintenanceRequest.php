<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('retry_after') === '') {
            $this->merge(['retry_after' => null]);
        }

        if ($this->input('bypass_secret') === '') {
            $this->merge(['bypass_secret' => null]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', Rule::in(['0', '1'])],
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'retry_after' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'bypass_secret' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'bypass_secret.regex' => 'Önizleme anahtarı yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
        ];
    }
}
