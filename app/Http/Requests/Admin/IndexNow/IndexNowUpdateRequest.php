<?php

namespace App\Http\Requests\Admin\IndexNow;

use Illuminate\Foundation\Http\FormRequest;

class IndexNowUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('indexnow.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'auto_submit' => ['nullable', 'boolean'],
        ];
    }
}
