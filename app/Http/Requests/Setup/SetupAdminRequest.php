<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SetupAdminRequest extends FormRequest
{
    use AuthorizesSetup;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'ad',
            'email' => 'e-posta',
            'password' => 'parola',
        ];
    }
}
