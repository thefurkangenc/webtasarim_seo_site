<?php

namespace App\Captcha;

use App\Captcha\Contracts\Driver;
use App\Captcha\Support\CaptchaException;
use App\Captcha\Support\PanelSettings;
use App\Captcha\Support\Signer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Captcha'nın tek giriş noktası. Bileşen, uç noktalar ve doğrulama kuralı
 * yalnızca bu sınıfı tanır; hangi sürücünün çalıştığını bilmezler.
 *
 * Akış üç adımdır:
 *   issue()  -> bulmaca + imzalı jeton (doğru cevap jetonun içinde, tarayıcıya gitmez)
 *   solve()  -> cevap doğruysa imzalı "bilet"
 *   valid()  -> formdaki bilet geçerli mi (consume() ile tek kullanımlık olur)
 */
class CaptchaManager
{
    /** Formdaki gizli alanın adı. */
    public const FIELD = 'captcha';

    private const CHALLENGE_KEY = 'captcha:challenge:';

    private const TICKET_KEY = 'captcha:ticket:';

    /** @var array<string, mixed>|null */
    private ?array $settings = null;

    private ?Driver $driver = null;

    public function enabled(): bool
    {
        return (bool) $this->setting('enabled', true) && $this->setting('drivers.'.$this->driverKey()) !== null;
    }

    /** Bu form için doğrulama isteniyor mu? */
    public function enabledFor(?string $form): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        return $form === null || (bool) $this->setting('forms.'.$form, false);
    }

    public function driver(): Driver
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        $class = $this->setting('drivers.'.$this->driverKey());

        if (! is_string($class) || ! class_exists($class)) {
            throw new RuntimeException('Captcha sürücüsü bulunamadı: '.$this->driverKey());
        }

        return $this->driver = app($class);
    }

    /**
     * Yeni bir bulmaca üretir ve tarayıcıya gidecek yükü döner.
     *
     * @return array<string, mixed>
     */
    public function issue(): array
    {
        $challenge = $this->driver()->challenge();
        $id = (string) Str::uuid();
        $ttl = (int) $this->setting('challenge_ttl', 300);

        /*
        | Doğru cevap YALNIZCA sunucuda durur. İmzalı jeton okunabilir bir
        | base64 gövdesidir — sır oraya konsaydı tarayıcı onu çözüp cevabı
        | okuyabilirdi. Jeton sadece hangi bulmaca olduğunu söyler; kayıt
        | Cache::pull ile çekildiği için her bulmaca tek denemede tükenir.
        */
        Cache::put(self::CHALLENGE_KEY.$id, $challenge->secret, $ttl);

        return [
            ...$challenge->payload,
            'token' => Signer::sign(['id' => $id], $ttl),
            'expires_in' => $ttl,
        ];
    }

    /**
     * Cevabı dener. Doğruysa forma konacak bileti, yanlışsa null döner.
     * Jeton doğru ya da yanlış her denemede tükenir — aynı bulmaca üzerinde
     * deneme yapılamaz.
     *
     * @param  array<string, mixed>  $answer
     */
    public function solve(string $token, array $answer): ?string
    {
        $payload = Signer::verify($token);
        $secret = $payload === null ? null : Cache::pull(self::CHALLENGE_KEY.($payload['id'] ?? ''));

        if (! is_array($secret) || ! $this->driver()->solved($answer, $secret)) {
            return null;
        }

        $id = (string) Str::uuid();
        $ttl = (int) $this->setting('ticket_ttl', 900);

        Cache::put(self::TICKET_KEY.$id, true, $ttl);

        return Signer::sign(['id' => $id], $ttl);
    }

    /** Uç noktanın kullandığı hali: yanlış cevapta 422 JSON'a çevrilen istisna. */
    public function solveOrFail(string $token, array $answer): string
    {
        return $this->solve($token, $answer)
            ?? throw new CaptchaException('Doğrulama tamamlanamadı. Parçayı yerine oturtmayı yeniden deneyin.');
    }

    /**
     * Formla gelen bileti doğrular ama TÜKETMEZ — form başka bir alandan
     * hata alırsa kullanıcı bulmacayı yeniden çözmek zorunda kalmasın diye.
     * Tüketme, doğrulamanın tamamı geçtikten sonra consume() ile yapılır.
     */
    public function valid(?string $ticket, ?string $form = null): bool
    {
        if (! $this->enabledFor($form)) {
            return true;
        }

        $payload = Signer::verify((string) $ticket);

        return $payload !== null && Cache::has(self::TICKET_KEY.($payload['id'] ?? ''));
    }

    /** Bileti yakar; aynı bilet ikinci bir gönderimde kullanılamaz. */
    public function consume(?string $ticket): void
    {
        $payload = Signer::verify((string) $ticket);

        if ($payload !== null) {
            Cache::forget(self::TICKET_KEY.($payload['id'] ?? ''));
        }
    }

    /** config/captcha + panel ayarları — nokta notasyonuyla okunur. */
    public function setting(string $key, mixed $default = null): mixed
    {
        if ($this->settings === null) {
            $this->settings = array_replace_recursive(
                (array) config('captcha', []),
                PanelSettings::overrides(),
            );
        }

        return data_get($this->settings, $key, $default);
    }

    private function driverKey(): string
    {
        return (string) $this->setting('driver', 'puzzle');
    }
}
