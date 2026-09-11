<?php

namespace App\Http\Requests\Admin\Lead;

use Illuminate\Foundation\Http\FormRequest;

class LeadReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('lead.reply');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'subject.required' => 'Konu zorunludur.',
            'body.required' => 'Yanıt metnini yazın.',
        ];
    }
}
