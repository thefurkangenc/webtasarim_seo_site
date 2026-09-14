<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetupMailRequest extends FormRequest
{
    use AuthorizesSetup;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if ($this->boolean('skipped')) {
            return [
                'skipped' => ['accepted'],
            ];
        }

        return [
            'skipped' => ['sometimes', 'boolean'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'encryption' => ['required', 'string', Rule::in(['tls', 'ssl', 'none'])],
            'from_name' => ['nullable', 'string', 'max:150'],
            'from_address' => ['required', 'email', 'max:150'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'host' => 'SMTP sunucusu',
            'from_address' => 'gönderen e-posta',
        ];
    }
}
