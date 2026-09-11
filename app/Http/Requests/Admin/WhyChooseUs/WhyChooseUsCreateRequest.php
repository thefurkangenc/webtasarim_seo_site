<?php

namespace App\Http\Requests\Admin\WhyChooseUs;

use Illuminate\Foundation\Http\FormRequest;

class WhyChooseUsCreateRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
