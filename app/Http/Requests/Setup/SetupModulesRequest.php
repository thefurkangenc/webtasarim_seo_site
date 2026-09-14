<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;

class SetupModulesRequest extends FormRequest
{
    use AuthorizesSetup;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $keys = array_keys(config('modules.definitions', []));

        return [
            'modules' => ['required', 'array'],
            'modules.*' => ['boolean'],
        ] + collect($keys)->mapWithKeys(fn (string $key) => [
            "modules.{$key}" => ['sometimes', 'boolean'],
        ])->all();
    }

    protected function prepareForValidation(): void
    {
        $modules = $this->input('modules', []);

        if (! is_array($modules)) {
            return;
        }

        $this->merge([
            'modules' => collect($modules)
                ->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN))
                ->all(),
        ]);
    }
}
