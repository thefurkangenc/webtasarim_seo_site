<?php

namespace App\Services\Profile;

use App\Models\Country\Country;
use App\Models\User;
use App\Support\Activity;
use App\Support\Phone;
use DomainException;
use Illuminate\Support\Facades\Hash;

/**
 * Kullanıcının kendi hesabı. Kullanıcı yönetimi modülünden (başkalarının
 * hesaplarını düzenlemek) ayrıdır: burada rol ya da yetki değiştirilemez,
 * yalnızca kimlik bilgileri, telefon ve şifre güncellenir.
 */
class ProfileService
{
    /** @return array<string, mixed> */
    public function formData(User $user): array
    {
        $user->loadMissing('country');

        return [
            'user' => $user,
            'avatar' => $user->getFirstMedia('avatar'),
            'countries' => Country::query()->active()->ordered()->get(),
            'roles' => $user->roles->map(fn ($role) => $role->label ?: $role->name)->all(),
            'permissionCount' => $user->hasRole('super-admin')
                ? null  // super-admin Gate::before ile her izne sahip; sayı anlamsız.
                : $user->getAllPermissions()->count(),
        ];
    }

    /** @param  array<string, mixed>  $data */
    public function update(User $user, array $data): User
    {
        $country = isset($data['country_id'])
            ? Country::query()->find($data['country_id'])
            : null;

        $phone = Phone::normalize($data['phone'] ?? null, $country);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'country_id' => $data['country_id'] ?? null,
            'phone' => $phone !== '' ? $phone : null,
        ]);

        $user->syncMedia($data['avatar_media_id'] ?? null, 'avatar');

        return $user;
    }

    /**
     * Şifre değişimi. Mevcut şifre doğrulaması BURADA yapılır, FormRequest'te
     * değil: `current_password` kuralı doğrulama hatası olarak döner ve alan
     * altına basılır, ama bu bir iş kuralıdır ve denetim kaydına düşmesi
     * gerekir — yanlış şifre denemeleri de bilgi taşır.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePassword(User $user, array $data): void
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            Activity::record(
                logName: 'user',
                event: 'password_failed',
                description: 'Şifre değiştirme denemesinde mevcut şifre yanlış girildi.',
                severity: 'warning',
            );

            throw new DomainException('Mevcut şifreniz doğru değil.');
        }

        // Model olayı tetiklenir ama diff'te şifre maskelenir
        // (config/activity-log.php > masked), bu yüzden ayrı bir kayıt düşülür.
        $user->update(['password' => $data['password']]);

        Activity::record(
            logName: 'user',
            event: 'password_changed',
            description: 'Hesap şifresi değiştirildi.',
        );
    }
}
