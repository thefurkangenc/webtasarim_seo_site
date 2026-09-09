<?php

namespace App\Http\Requests\Admin\Role;

class RoleUpdateRequest extends RoleCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('role.update');
    }
}
