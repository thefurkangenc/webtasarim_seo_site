<?php

namespace App\Http\Requests\Contact;

use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:200'],
            'privacy' => [
                Rule::requiredIf(fn () => Settings::bool('contact.privacy_required', true)),
                'accepted',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'privacy.required' => 'Devam etmek için aydınlatma metnini kabul etmelisiniz.',
            'privacy.accepted' => 'Devam etmek için aydınlatma metnini kabul etmelisiniz.',
        ];
    }
}
