<?php

namespace App\Captcha\Concerns;

use App\Captcha\CaptchaManager;
use App\Captcha\Rules\Captcha;

/**
 * FormRequest'e captcha doğrulaması ekler.
 *
 *     class ContactSubmitRequest extends FormRequest
 *     {
 *         use VerifiesCaptcha;
 *
 *         protected function captchaForm(): ?string { return 'contact'; }
 *
 *         public function rules(): array
 *         {
 *             return [..., CaptchaManager::FIELD => $this->captchaRules()];
 *         }
 *     }
 *
 * Bilet passedValidation()'da yakılır: form baştan sona geçtiyse doğrulama
 * harcanır, başka bir alan hata verdiyse kullanıcı bulmacayı korur.
 *
 * Kendi passedValidation()'ını yazan bir Request bu trait'i kullanacaksa
 * metodun içinden parent::passedValidation() çağırmayı unutma.
 */
trait VerifiesCaptcha
{
    /** Hangi form — config/ayarlardaki `forms` anahtarlarıyla eşleşir. */
    protected function captchaForm(): ?string
    {
        return null;
    }

    /** @return array<int, mixed> */
    protected function captchaRules(): array
    {
        return [new Captcha($this->captchaForm())];
    }

    protected function passedValidation(): void
    {
        app(CaptchaManager::class)->consume((string) $this->input(CaptchaManager::FIELD));
    }
}
