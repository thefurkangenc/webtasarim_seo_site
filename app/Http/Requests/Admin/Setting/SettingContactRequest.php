<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', Rule::in(['0', '1'])],
            'to_email' => ['nullable', 'email', 'max:150'],
            'cc_email' => ['nullable', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'success_message' => ['required', 'string', 'max:500'],
            'error_message' => ['required', 'string', 'max:500'],
            'auto_reply_enabled' => ['required', Rule::in(['0', '1'])],
            'auto_reply_subject' => ['nullable', 'required_if:auto_reply_enabled,1', 'string', 'max:255'],
            'auto_reply_body' => ['nullable', 'required_if:auto_reply_enabled,1', 'string', 'max:10000'],
            'privacy_required' => ['required', Rule::in(['0', '1'])],
            'privacy_text' => ['nullable', 'required_if:privacy_required,1', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'auto_reply_subject.required_if' => 'Otomatik yanıt açıkken konu zorunludur.',
            'auto_reply_body.required_if' => 'Otomatik yanıt açıkken metin zorunludur.',
            'privacy_text.required_if' => 'Onay metni, KVKK onayı zorunluyken boş bırakılamaz.',
        ];
    }
}
