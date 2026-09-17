<?php

namespace App\Captcha\Http;

use App\Captcha\CaptchaManager;
use Illuminate\Foundation\Http\FormRequest;

class SolveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:4000'],
            ...app(CaptchaManager::class)->driver()->rules(),
        ];
    }
}
