<?php

namespace App\Http\Requests\Admin\IndexNow;

use Illuminate\Foundation\Http\FormRequest;

class IndexNowSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('indexnow.submit');
    }

    /** Satır satır girilen adresler; yalnızca yol yazılmışsa tam adrese çevrilir. */
    protected function prepareForValidation(): void
    {
        $lines = preg_split('/\r?\n/', (string) $this->input('urls'));

        $this->merge(['urls' => collect($lines)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(fn (string $line) => preg_match('#^https?://#i', $line) ? $line : url('/'.ltrim($line, '/')))
            ->unique()
            ->values()
            ->implode("\n"),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'urls' => ['required', 'string', 'max:20000'],
        ];
    }

    /** @return list<string> */
    public function urls(): array
    {
        return array_values(array_filter(explode("\n", (string) $this->validated('urls'))));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'urls.required' => 'En az bir adres girin.',
        ];
    }
}
