<?php

namespace App\Http\Requests\Admin\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('lead.bulk');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['read', 'unread', 'status', 'delete', 'restore'])],
            // Yalnızca "status" işleminde anlamlı.
            'status' => ['nullable', 'required_if:action,status', Rule::in(array_keys(config('leads.statuses')))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ids.required' => 'En az bir kayıt seçin.',
            'status.required_if' => 'Atanacak durumu seçin.',
        ];
    }
}
