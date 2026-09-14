<?php

namespace App\Http\Requests\Admin\Profile;

use App\Http\Requests\Concerns\ValidatesPhoneFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    use ValidatesPhoneFields;

    /**
     * Herkes KENDİ hesabını düzenleyebilir; ayrı bir izin aranmaz. Düzenlenen
     * kayıt her zaman oturum sahibidir (controller route parametresi almaz),
     * bu yüzden başkasının hesabına dokunmak mümkün değil.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'avatar_media_id' => ['nullable', 'integer', 'exists:media,id'],
            ...$this->phoneRules(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => 'Bu e-posta adresi başka bir hesapta kullanılıyor.',
        ];
    }
}
