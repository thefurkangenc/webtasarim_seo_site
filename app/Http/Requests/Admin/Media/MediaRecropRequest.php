<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaRecropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.update');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('crop'))) {
            $this->merge(['crop' => json_decode($this->input('crop'), true)]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'crop' => ['required', 'array'],
            'crop.x' => ['required', 'numeric'],
            'crop.y' => ['required', 'numeric'],
            'crop.width' => ['required', 'numeric', 'min:1'],
            'crop.height' => ['required', 'numeric', 'min:1'],
            'crop.rotate' => ['nullable', 'numeric'],
            'crop.scaleX' => ['nullable', 'numeric'],
            'crop.scaleY' => ['nullable', 'numeric'],
        ];
    }
}
