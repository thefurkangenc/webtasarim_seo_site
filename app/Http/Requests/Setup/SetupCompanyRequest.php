<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;

class SetupCompanyRequest extends FormRequest
{
    use AuthorizesSetup;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('logo_media_id') === '' || $this->input('logo_media_id') === null) {
            $this->merge(['logo_media_id' => null]);
        }
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'firma adı',
            'email' => 'e-posta',
        ];
    }
}
