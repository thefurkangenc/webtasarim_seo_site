<?php

namespace App\Http\Requests\Admin\Auth;

use App\Captcha\CaptchaManager;
use App\Captcha\Concerns\VerifiesCaptcha;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use VerifiesCaptcha;

    public function authorize(): bool
    {
        return true;
    }

    protected function captchaForm(): ?string
    {
        return 'login';
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            CaptchaManager::FIELD => $this->captchaRules(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'password.required' => 'Parola zorunludur.',
        ];
    }
}
