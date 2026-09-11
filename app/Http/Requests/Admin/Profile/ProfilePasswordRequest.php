<?php

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Doğruluğu serviste kontrol edilir (denetim kaydına da düşsün diye).
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Mevcut şifrenizi girin.',
            'password.required' => 'Yeni şifreyi girin.',
            'password.confirmed' => 'Yeni şifre ile tekrarı aynı değil.',
        ];
    }
}
