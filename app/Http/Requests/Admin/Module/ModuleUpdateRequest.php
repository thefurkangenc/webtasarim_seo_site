<?php

namespace App\Http\Requests\Admin\Module;

use Illuminate\Foundation\Http\FormRequest;

class ModuleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('module.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'modules' => ['required', 'array'],
            'modules.*.name' => ['nullable', 'string', 'max:255'],
            'modules.*.is_active' => ['required', 'boolean'],

            'presets' => ['nullable', 'array'],
            'presets.*.width' => ['required', 'integer', 'min:16', 'max:4000'],
            'presets.*.height' => ['required', 'integer', 'min:16', 'max:4000'],
        ];
    }
}
