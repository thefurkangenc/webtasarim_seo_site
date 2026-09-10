<?php

namespace App\Listeners;

use App\Support\Activity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

/**
 * Oturum olaylarını denetim kaydına yazar.
 *
 * AuthService'i düzenlemek yerine Laravel'in kendi olaylarına bağlanıyoruz:
 * "beni hatırla" ile otomatik giriş, parola sıfırlama sonrası giriş ve
 * hız sınırı kilidi gibi servisten geçmeyen durumlar da böylece yakalanır.
 *
 * Kayıt AppServiceProvider::boot() içinde Event::subscribe() ile yapılır.
 *
 * GÜVENLİK: Denenen parola hiçbir koşulda saklanmaz — başarısız girişte
 * yalnızca denenen e-posta adresi kaydedilir.
 */
class LogAuthenticationActivity
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
            Lockout::class => 'onLockout',
            PasswordReset::class => 'onPasswordReset',
        ];
    }

    public function onLogin(Login $event): void
    {
        Activity::record(
            logName: 'auth',
            event: 'login',
            description: "Panele giriş yapıldı: {$event->user->email}",
            subject: $event->user,
            subjectLabel: $event->user->name,
            causer: $event->user,
        );
    }

    public function onLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        Activity::record(
            logName: 'auth',
            event: 'logout',
            description: "Oturum kapatıldı: {$event->user->email}",
            subject: $event->user,
            subjectLabel: $event->user->name,
            causer: $event->user,
        );
    }

    public function onFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'bilinmiyor';

        Activity::record(
            logName: 'auth',
            event: 'login_failed',
            description: "Başarısız giriş denemesi: {$email}",
            // Kullanıcı varsa konu olarak bağlanır; yoksa (var olmayan hesap)
            // yalnızca denenen e-posta açıklamada kalır.
            subject: $event->user,
            subjectLabel: $email,
            // Parola ASLA saklanmaz; yalnızca hangi hesabın denendiği.
            properties: ['new' => ['email' => $email, 'guard' => $event->guard]],
            // Başarısız giriş oturumsuzdur — fail bilinmiyor.
            causer: $event->user,
        );
    }

    public function onLockout(Lockout $event): void
    {
        Activity::record(
            logName: 'security',
            event: 'lockout',
            description: 'Çok fazla başarısız giriş nedeniyle giriş geçici olarak kilitlendi.',
            properties: ['new' => ['email' => $event->request->input('email')]],
        );
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        Activity::record(
            logName: 'auth',
            event: 'password_changed',
            description: "Parola sıfırlandı: {$event->user->email}",
            subject: $event->user,
            subjectLabel: $event->user->name,
            causer: $event->user,
        );
    }
}
