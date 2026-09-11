<?php

namespace App\Http\Requests\Admin\Popup;

use App\Http\Requests\Concerns\ValidatesAudience;
use Illuminate\Foundation\Http\FormRequest;

class PopupCreateRequest extends FormRequest
{
    use ValidatesAudience;

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'heading' => ['nullable', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:2000'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:500', 'required_with:button_label'],
            'collect_email' => ['nullable', 'boolean'],
            'delay_seconds' => ['nullable', 'integer', 'between:0,'.config('notices.popup_delay_max', 30)],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            ...$this->audienceRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $this->withAudienceValidator($validator);
    }
}
