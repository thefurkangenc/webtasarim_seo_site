<?php

namespace App\Http\Requests\Admin\ProjectCategory;

use App\Http\Requests\Concerns\ValidatesSharedFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectCategoryCreateRequest extends FormRequest
{
    use ValidatesSharedFields;

    public function authorize(): bool
    {
        return $this->user()->can('project-category.store');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150',
                Rule::unique('project_categories', 'slug')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],

            ...$this->seoRules(),
        ];
    }
}
