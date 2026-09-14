<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UserCreateRequest;
use App\Http\Requests\Admin\User\UserFilterRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly UserService $service) {}

    public function index(): View
    {
        return view('admin.pages.user.index', $this->service->indexData());
    }

    public function create(): View
    {
        return view('admin.pages.user.form', $this->service->formData(null, auth()->user()));
    }

    public function edit(User $user): View
    {
        abort_unless($this->service->canView($user, auth()->user()), 403);

        return view('admin.pages.user.form', $this->service->formData($user, auth()->user()));
    }

    public function datatable(UserFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated(), $request->user()));
    }

    public function store(UserCreateRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return $this->success('Kullanıcı eklendi.', [
            'id' => $user->id,
            'redirect' => route('admin.user.edit', $user),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $this->service->update($user, $request->validated(), $request->user());

        return $this->success('Kullanıcı güncellendi.');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user, auth()->user());

        return $this->success('Kullanıcı silindi.');
    }
}
