<?php

namespace App\Http\Requests\Admin\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityLogFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:64'],
            'event' => ['nullable', 'string', 'max:40'],
            'severity' => ['nullable', Rule::in(array_keys(config('activity-log.severities')))],
            'device_type' => ['nullable', Rule::in(['desktop', 'mobile', 'tablet', 'bot'])],
            'ip' => ['nullable', 'string', 'max:45'],
            'causer_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],

            // Modül modalı ve satır bazlı geçmiş bu ikisini gönderir.
            'subject_type' => ['nullable', 'string', 'max:191'],
            'subject_id' => ['nullable', 'integer'],

            'sort' => ['nullable', Rule::in(['created_at', 'event', 'log_name', 'severity'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz.',
        ];
    }
}
