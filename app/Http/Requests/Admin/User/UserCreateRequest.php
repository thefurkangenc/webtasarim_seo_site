<?php

namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Concerns\ValidatesPhoneFields;
use App\Models\Role\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserCreateRequest extends FormRequest
{
    use ValidatesPhoneFields;

    public function authorize(): bool
    {
        return $this->user()->can('user.store');
    }

    protected function prepareForValidation(): void
    {
        $this->preparePhone();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->whereNot('name', Role::SUPER_ADMIN),
            ],
            'is_active' => ['required', 'boolean'],
            'avatar_media_id' => ['nullable', 'integer', 'exists:media,id'],
            ...$this->phoneRules(),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'ad soyad',
            'email' => 'e-posta',
            'password' => 'şifre',
            'role_id' => 'rol',
            'is_active' => 'hesap durumu',
            'country_id' => 'ülke',
            'phone' => 'telefon',
            'avatar_media_id' => 'profil fotoğrafı',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => 'Bu e-posta adresi başka bir hesapta kullanılıyor.',
            'password.confirmed' => 'Şifre ile tekrarı aynı değil.',
            'role_id.exists' => 'Seçilen rol atanamaz.',
        ];
    }
}
