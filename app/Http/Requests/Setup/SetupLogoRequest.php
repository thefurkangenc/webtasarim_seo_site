<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;

class SetupLogoRequest extends FormRequest
{
    use AuthorizesSetup;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'logo' => ['required', 'file', 'image', 'max:4096'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'logo' => 'logo',
        ];
    }
}
