<?php

namespace App\Http\Requests\Concerns;

use App\Models\Country\Country;
use App\Support\Phone;
use Closure;

trait ValidatesPhoneFields
{
    /**
     * Telefon isteğe bağlıdır; doluysa ülke seçilmiş olmalı ve hane sayısı
     * o ülkenin maskesiyle birebir örtüşmeli.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function phoneRules(): array
    {
        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $country = Country::query()->find($this->input('country_id'));

                    if (! $country) {
                        $fail('Telefon için bir ülke seçin.');

                        return;
                    }

                    $digits = Phone::normalize((string) $value, $country);

                    if ($digits === '' || strlen($digits) !== $country->digitCount()) {
                        $fail("Bu ülke için {$country->digitCount()} haneli numara girin.");
                    }
                },
            ],
        ];
    }

    protected function preparePhone(): void
    {
        $country = Country::query()->find($this->input('country_id'));
        $digits = Phone::normalize($this->input('phone'), $country);

        $this->merge([
            'phone' => $digits !== '' ? $digits : null,
            'country_id' => $this->input('country_id') ?: null,
        ]);
    }
}
