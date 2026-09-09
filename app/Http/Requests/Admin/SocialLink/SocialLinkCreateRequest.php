<?php

namespace App\Http\Requests\Admin\SocialLink;

use Illuminate\Foundation\Http\FormRequest;

class SocialLinkCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('social-link.store');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:255'],
            'icon_media_id' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'icon_media_id.required' => 'İkon zorunludur.',
        ];
    }
}
