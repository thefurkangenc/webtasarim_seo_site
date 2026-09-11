<?php

namespace App\Http\Requests\Admin\Announcement;

use App\Http\Requests\Concerns\ValidatesAudience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementCreateRequest extends FormRequest
{
    use ValidatesAudience;

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:500'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:500', 'required_with:button_label'],
            'tone' => ['required', Rule::in(array_keys(config('notices.tones')))],
            ...$this->audienceRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $this->withAudienceValidator($validator);
    }
}
