<?php

namespace App\Http\Requests\Setup;

use App\Http\Requests\Setup\Concerns\AuthorizesSetup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetupRunRequest extends FormRequest
{
    use AuthorizesSetup;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tasks = collect(config('setup.tasks'))->pluck('key')->all();

        return [
            'task' => ['required', 'string', Rule::in($tasks)],
        ];
    }
}
