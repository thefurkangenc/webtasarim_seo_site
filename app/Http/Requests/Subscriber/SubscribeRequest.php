<?php

namespace App\Http\Requests\Subscriber;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'source' => ['nullable', Rule::in(array_keys(config('subscribers.sources')))],
            'website' => ['nullable', 'string', 'max:200'],
            'privacy' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'privacy.accepted' => 'Abone olmak için aydınlatma metnini kabul etmelisiniz.',
        ];
    }
}
