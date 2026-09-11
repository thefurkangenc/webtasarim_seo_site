<?php

namespace App\Http\Requests\Admin\SearchConsole;

use Illuminate\Foundation\Http\FormRequest;

class SearchConsoleInspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('search-console.inspect');
    }

    /** Kullanıcı yalnızca yol yazdıysa (/hakkimizda) tam adrese çevrilir. */
    protected function prepareForValidation(): void
    {
        $url = trim((string) $this->input('url'));

        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            $this->merge(['url' => url('/'.ltrim($url, '/'))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:2048', 'url'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'url.required' => 'Denetlenecek adresi girin.',
            'url.url' => 'Geçerli bir adres girin (ör. /hakkimizda).',
        ];
    }
}
