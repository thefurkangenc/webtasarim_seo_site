<?php

namespace App\Http\Requests\Admin\SearchConsole;

use Illuminate\Foundation\Http\FormRequest;

class SearchConsoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('search-console.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            // İki geçerli biçim var: "https://siteniz.com/" (adres öneki mülkü)
            // ve "sc-domain:siteniz.com" (alan adı mülkü).
            'site_url' => ['nullable', 'string', 'max:255', 'regex:#^(https?://[^\s]+|sc-domain:[^\s/]+)$#i'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'site_url.regex' => 'Adres "https://siteniz.com/" ya da "sc-domain:siteniz.com" biçiminde olmalı.',
        ];
    }
}
