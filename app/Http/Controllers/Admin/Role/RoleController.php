<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\RoleCreateRequest;
use App\Http\Requests\Admin\Role\RoleFilterRequest;
use App\Http\Requests\Admin\Role\RoleUpdateRequest;
use App\Models\Role\Role;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly RoleService $service) {}

    public function index(): View
    {
        return view('admin.pages.role.index');
    }

    public function create(): View
    {
        return view('admin.pages.role.form', $this->service->formData());
    }

    public function edit(Role $role): View
    {
        abort_if($role->isProtected(), 404);

        return view('admin.pages.role.form', $this->service->formData($role));
    }

    public function datatable(RoleFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(RoleCreateRequest $request): JsonResponse
    {
        $role = $this->service->create($request->validated());

        return $this->success('Rol eklendi.', [
            ...$role->toPayload(),
            'redirect' => route('admin.role.edit', $role),
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role): JsonResponse
    {
        return $this->success('Rol güncellendi.', $this->service->update($role, $request->validated())->toPayload());
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->service->delete($role);

        return $this->success('Rol silindi.');
    }
}
