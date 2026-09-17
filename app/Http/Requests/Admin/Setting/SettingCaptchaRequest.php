<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingCaptchaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.captcha.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', Rule::in(['0', '1'])],
            'driver' => ['required', Rule::in(array_keys((array) config('captcha.drivers', [])))],
            'form_contact' => ['required', Rule::in(['0', '1'])],
            'form_quote' => ['required', Rule::in(['0', '1'])],
            'form_newsletter' => ['required', Rule::in(['0', '1'])],
            'form_login' => ['required', Rule::in(['0', '1'])],
            'tolerance' => ['required', 'integer', 'min:2', 'max:20'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'driver.in' => 'Geçerli bir doğrulama türü seçin.',
            'tolerance.min' => 'Sapma payı en az 2 piksel olabilir.',
            'tolerance.max' => 'Sapma payı en fazla 20 piksel olabilir.',
        ];
    }
}
