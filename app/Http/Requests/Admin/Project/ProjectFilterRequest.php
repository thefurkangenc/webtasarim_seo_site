<?php

namespace App\Http\Requests\Admin\Project;

use App\Models\Project\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectFilterRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Project::STATUSES))],
            'project_category_id' => ['nullable', 'integer'],
            'service_id' => ['nullable', 'integer'],
            // Checkbox filtresi: işaretsizken hiç gönderilmez (bkz. core/table.js).
            'featured' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['title', 'status', 'client_name', 'completed_at', 'sort_order', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
