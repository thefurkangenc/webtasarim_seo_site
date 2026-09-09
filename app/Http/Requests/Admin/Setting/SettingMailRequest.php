<?php

namespace App\Http\Requests\Admin\Setting;

use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.mail.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => [
                Rule::requiredIf(fn () => blank(Settings::get('mail.password'))),
                'nullable',
                'string',
                'max:255',
            ],
            'encryption' => ['required', 'string', Rule::in(['tls', 'ssl', 'none'])],
            'from_name' => ['nullable', 'string', 'max:150'],
            'from_address' => ['required', 'email', 'max:150'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.required' => 'Şifre zorunludur.',
            'encryption.in' => 'Şifreleme TLS, SSL veya Yok olmalıdır.',
        ];
    }
}
