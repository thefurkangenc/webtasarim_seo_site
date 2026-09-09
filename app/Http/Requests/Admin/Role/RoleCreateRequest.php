<?php

namespace App\Http\Requests\Admin\Role;

use App\Models\Role\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('role.create');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $this->input('guard_name', 'web'))
                    ->ignore($this->route('role')),
                Rule::notIn([Role::SUPER_ADMIN]),
            ],
            'label' => ['required', 'string', 'max:100'],
            'guard_name' => ['required', 'string', Rule::in(array_keys(Role::GUARDS))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'rol adı',
            'label' => 'görünen ad',
            'guard_name' => 'guard',
            'permissions' => 'yetkiler',
            'permissions.*' => 'yetki',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.regex' => 'Rol adı yalnızca küçük harf, rakam, tire ve alt çizgi içerebilir.',
            'name.not_in' => 'Bu rol adı kullanılamaz.',
        ];
    }
}
