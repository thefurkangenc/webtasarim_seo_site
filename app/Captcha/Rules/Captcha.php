<?php

namespace App\Captcha\Rules;

use App\Captcha\CaptchaManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Formdaki gizli "captcha" alanını doğrular.
 *
 * Bileti yakmaz — bunu doğrulamanın tamamı geçtikten sonra
 * App\Captcha\Concerns\VerifiesCaptcha yapar. Aksi halde kullanıcı e-posta
 * alanına yanlış bir şey yazdığında bulmacayı baştan çözmek zorunda kalırdı.
 */
class Captcha implements ValidationRule
{
    /*
     * Laravel özel kuralları, alan istekte HİÇ yoksa varsayılan olarak
     * atlar — o zaman "captcha" alanını göndermeyen bir bot doğrulamayı
     * tamamen es geçerdi. Bu bayrak (InvokableValidationRule::make onu
     * okur) kuralı alan yokken de çalıştırır.
     */
    public bool $implicit = true;

    public function __construct(private readonly ?string $form = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ticket = is_string($value) && strlen($value) <= 4000 ? $value : null;

        if (! app(CaptchaManager::class)->valid($ticket, $this->form)) {
            $fail('Devam etmek için güvenlik doğrulamasını tamamlayın.');
        }
    }
}
