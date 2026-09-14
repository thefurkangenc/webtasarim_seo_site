<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Kimlik bilgilerini doğrular ve oturumu açar.
     *
     * @param  array<string, mixed>  $credentials
     *
     * @throws ValidationException Kimlik bilgileri hatalıysa veya hesap pasifse
     */
    public function login(array $credentials, bool $remember = false): void
    {
        $user = User::query()->where('email', $credentials['email'] ?? null)->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Bu hesap pasif durumda. Yöneticinizle görüşün.',
            ]);
        }

        if (! Auth::attempt(Arr::only($credentials, ['email', 'password']), $remember)) {
            throw ValidationException::withMessages([
                'email' => 'E-posta adresi veya parola hatalı.',
            ]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
