<?php

namespace App\Http\Requests\Admin\Testimonial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestimonialCreateRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
