<?php

namespace App\Http\Requests\Admin\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('lead.index');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_keys(config('leads.statuses')))],
            'source' => ['nullable', Rule::in(array_keys(config('leads.sources')))],
            // "none" = atanmamış talepler.
            'assigned_to' => ['nullable', 'string', 'max:20'],
            'unread' => ['nullable', Rule::in(['0', '1'])],
            'trashed' => ['nullable', Rule::in(['0', '1'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'sort' => ['nullable', Rule::in(['created_at', 'name', 'email', 'status'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
