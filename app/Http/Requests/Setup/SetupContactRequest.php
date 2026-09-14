<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;

class SetupContactRequest extends FormRequest
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
            'enabled' => ['sometimes', 'boolean'],
            'to_email' => ['nullable', 'email', 'max:150'],
            'cc_email' => ['nullable', 'email', 'max:150'],
            'auto_reply_enabled' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'to_email' => 'alıcı e-posta',
            'cc_email' => 'bilgi e-posta',
        ];
    }
}
