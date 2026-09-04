<?php

namespace App\Http\Requests\Admin\Integration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IntegrationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.update');
    }

    protected function prepareForValidation(): void
    {
        $key = (string) $this->route('key');

        if ($key === 'tawk') {
            $property = trim((string) $this->input('property_id', ''));
            $widget = trim((string) $this->input('widget_id', ''));

            if (preg_match('#embed\.tawk\.to/([A-Fa-f0-9]+)/([A-Za-z0-9_-]+)#', $property, $match)) {
                $property = $match[1];
                $widget = $widget !== '' ? $widget : $match[2];
            }

            $this->merge([
                'property_id' => $property,
                'widget_id' => trim($widget),
            ]);
        }

        foreach (['phone', 'message', 'api_key'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return match ((string) $this->route('key')) {
            'whatsapp' => [
                'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s()\-]{10,20}$/'],
                'message' => ['nullable', 'string', 'max:500'],
                'position' => ['required', Rule::in(['left', 'right'])],
            ],
            'tawk' => [
                'property_id' => ['required', 'string', 'max:40', 'regex:/^[A-Fa-f0-9]{24}$/'],
                'widget_id' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            ],
            'phone' => [
                'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s()\-]{10,20}$/'],
                'position' => ['required', Rule::in(['left', 'right'])],
            ],
            'maps' => [
                'api_key' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]{20,128}$/'],
            ],
            default => [],
        };
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Telefon numarasını 05xx veya +90 5xx biçiminde yazın.',
            'property_id.regex' => 'Property ID 24 karakterlik bir kod olmalıdır. Tawk.to kodundaki embed.tawk.to adresini yapıştırabilirsiniz.',
            'widget_id.regex' => 'Widget ID geçersiz.',
            'api_key.regex' => 'Google Maps API anahtarı geçersiz görünüyor.',
        ];
    }
}
