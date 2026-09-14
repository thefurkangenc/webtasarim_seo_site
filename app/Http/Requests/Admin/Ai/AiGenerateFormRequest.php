<?php

namespace App\Http\Requests\Admin\Ai;

use App\Models\Ai\AiPrompt;
use Illuminate\Foundation\Http\FormRequest;

class AiGenerateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(
            AiPrompt::permissionForKey((string) $this->route('key')),
        );
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
