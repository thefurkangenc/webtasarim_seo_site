<?php

namespace App\Http\Requests\Quote;

use App\Captcha\CaptchaManager;
use App\Captcha\Concerns\VerifiesCaptcha;
use App\Models\Service\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteSubmitRequest extends FormRequest
{
    use VerifiesCaptcha;

    public function authorize(): bool
    {
        return true;
    }

    protected function captchaForm(): ?string
    {
        return 'quote';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:150'],
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('status', Service::STATUS_PUBLISHED),
            ],
            'region_id' => ['nullable', 'integer', 'exists:service_regions,id'],
            'phone' => ['required', 'string', 'regex:/^0 \(\d{3}\) \d{3} \d{2} \d{2}$/'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:200'],
            CaptchaManager::FIELD => $this->captchaRules(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'company.required' => 'Firma adını yazın.',
            'service_id.required' => 'Bir hizmet seçin.',
            'service_id.exists' => 'Bir hizmet seçin.',
            'phone.required' => 'Geçerli bir telefon numarası yazın.',
            'phone.regex' => 'Geçerli bir telefon numarası yazın.',
        ];
    }
}
