<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingCookieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.cookie.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', Rule::in(['0', '1'])],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'accept_label' => ['required', 'string', 'max:80'],
            'reject_label' => ['required', 'string', 'max:80'],
            'customize_label' => ['required', 'string', 'max:80'],
            'save_label' => ['required', 'string', 'max:80'],
            'policy_label' => ['required', 'string', 'max:80'],
            'necessary_title' => ['required', 'string', 'max:80'],
            'necessary_description' => ['required', 'string', 'max:500'],
            'functional_title' => ['required', 'string', 'max:80'],
            'functional_description' => ['required', 'string', 'max:500'],
            'analytics_title' => ['required', 'string', 'max:80'],
            'analytics_description' => ['required', 'string', 'max:500'],
            'marketing_title' => ['required', 'string', 'max:80'],
            'marketing_description' => ['required', 'string', 'max:500'],
            'lifetime_days' => ['required', 'integer', 'min:1', 'max:730'],
            'version' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
