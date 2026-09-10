<?php

namespace App\Http\Requests\Admin\Setting;

use App\Services\Setting\SettingService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SettingAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.analytics.update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'property_id' => preg_replace('/\D+/', '', (string) $this->input('property_id')),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'string', 'regex:/^\d{6,15}$/'],
            'service_account' => [
                'nullable', 'file', 'max:16',
                'extensions:json,txt',
                'mimetypes:application/json,text/plain,application/octet-stream',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $file = $this->file('service_account');
            $hasStored = filled(app(SettingService::class)->get('analytics', 'service_account'));

            if (! $file && ! $hasStored) {
                $validator->errors()->add('service_account', 'Service account JSON dosyasını yükleyin.');

                return;
            }

            if ($file) {
                $data = json_decode((string) $file->get(), true);

                if (! is_array($data) || blank($data['client_email'] ?? null) || blank($data['private_key'] ?? null)) {
                    $validator->errors()->add(
                        'service_account',
                        'Bu dosya geçerli bir service account JSON değil (client_email / private_key eksik).',
                    );
                }
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'property_id.required' => 'GA4 property ID zorunludur.',
            'property_id.regex' => 'Property ID yalnızca rakamlardan oluşur (örn. 493819123). Bu, G- ile başlayan ölçüm kimliği değildir.',
            'service_account.extensions' => 'Yalnızca .json dosyası yükleyin.',
            'service_account.mimetypes' => 'Yalnızca .json dosyası yükleyin.',
            'service_account.max' => 'Dosya çok büyük — service account JSON birkaç KB olmalı.',
        ];
    }
}
