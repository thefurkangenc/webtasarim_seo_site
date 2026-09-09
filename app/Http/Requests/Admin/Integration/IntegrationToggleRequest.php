<?php

namespace App\Http\Requests\Admin\Integration;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationToggleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('integration.toggle');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
        ];
    }
}
