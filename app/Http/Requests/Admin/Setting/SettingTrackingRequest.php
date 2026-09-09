<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.tracking.update');
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'ga4_id', 'gtm_id', 'google_site_verification',
            'meta_pixel_id', 'tiktok_pixel_id', 'linkedin_partner_id',
            'yandex_metrica_id', 'yandex_verification',
            'bing_uet_id', 'bing_verification',
            'head_scripts', 'body_scripts',
        ];

        $trimmed = [];

        foreach ($fields as $field) {
            if ($this->exists($field)) {
                $trimmed[$field] = is_string($this->input($field))
                    ? trim($this->input($field))
                    : $this->input($field);
            }
        }

        $this->merge($trimmed);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'ga4_id' => ['nullable', 'string', 'max:20', 'regex:/^G-[A-Z0-9]+$/i'],
            'gtm_id' => ['nullable', 'string', 'max:20', 'regex:/^GTM-[A-Z0-9]+$/i'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'meta_pixel_id' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:40', 'regex:/^[A-Z0-9]+$/i'],
            'linkedin_partner_id' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'yandex_metrica_id' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'yandex_verification' => ['nullable', 'string', 'max:255'],
            'bing_uet_id' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'bing_verification' => ['nullable', 'string', 'max:255'],
            'head_scripts' => ['nullable', 'string', 'max:20000'],
            'body_scripts' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ga4_id.regex' => 'GA4 kimliği G- ile başlamalıdır.',
            'gtm_id.regex' => 'GTM kimliği GTM- ile başlamalıdır.',
            'meta_pixel_id.regex' => 'Meta Pixel kimliği yalnızca rakam olmalıdır.',
            'tiktok_pixel_id.regex' => 'TikTok Pixel kimliği geçersiz.',
            'linkedin_partner_id.regex' => 'LinkedIn Partner kimliği yalnızca rakam olmalıdır.',
            'yandex_metrica_id.regex' => 'Yandex Metrica kimliği yalnızca rakam olmalıdır.',
            'bing_uet_id.regex' => 'Bing UET kimliği yalnızca rakam olmalıdır.',
        ];
    }
}
