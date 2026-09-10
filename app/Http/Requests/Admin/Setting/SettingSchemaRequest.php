<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingSchemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.schema.update');
    }

    /** Gün gün saat alanlarını tek JSON değere indirger. */
    protected function prepareForValidation(): void
    {
        $hours = $this->input('hours');

        if (is_array($hours)) {
            $clean = [];

            foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
                $row = $hours[$day] ?? [];
                $clean[$day] = [
                    'closed' => (bool) ($row['closed'] ?? false),
                    'opens' => substr(trim((string) ($row['opens'] ?? '')), 0, 5),
                    'closes' => substr(trim((string) ($row['closes'] ?? '')), 0, 5),
                ];
            }

            $this->merge(['opening_hours' => json_encode($clean, JSON_UNESCAPED_UNICODE)]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'business_type' => ['required', Rule::in(['Organization', 'ProfessionalService', 'LocalBusiness', 'Corporation'])],
            'founding_year' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:'.date('Y')],
            'tax_id' => ['nullable', 'string', 'max:20'],
            'tax_office' => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'max:12'],
            'area_served' => ['nullable', 'string', 'max:500'],
            'same_as' => ['nullable', 'string', 'max:2000'],
            'search_url' => [
                'nullable', 'string', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // {query} yer tutucusu URL'i teknik olarak geçersiz kılar;
                    // doğrulamadan önce örnek bir kelimeyle değiştir.
                    $probe = str_replace(['{query}', '{search}', '{search_term_string}'], 'test', (string) $value);

                    if (! filter_var($probe, FILTER_VALIDATE_URL) || ! str_starts_with($probe, 'http')) {
                        $fail('Geçerli bir adres girin (örn. https://site.com/ara?q={query}).');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'opening_hours' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function validated($key = null, $default = null): array
    {
        // hours[] doğrulama dizisinde tutulmaz; yalnızca türetilmiş JSON kaydedilir.
        return array_diff_key(parent::validated(), array_flip(['hours']));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'business_type.required' => 'İşletme türü seçilmelidir.',
            'business_type.in' => 'Geçerli bir işletme türü seçin.',
            'founding_year.digits' => 'Kuruluş yılı 4 haneli olmalı.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'business_type' => 'işletme türü',
            'founding_year' => 'kuruluş yılı',
            'price_range' => 'fiyat aralığı',
            'area_served' => 'hizmet bölgesi',
            'search_url' => 'site içi arama adresi',
            'description' => 'kısa tanım',
        ];
    }
}
