<?php

namespace App\Http\Requests\Admin\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('lead.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(config('leads.statuses')))],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => 'Durum seçilmelidir.',
            'assigned_to.exists' => 'Seçilen kullanıcı bulunamadı.',
        ];
    }
}
