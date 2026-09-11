<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\ProfilePasswordRequest;
use App\Http\Requests\Admin\Profile\ProfileUpdateRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ProfileService $service) {}

    public function edit(): View
    {
        return view('admin.pages.profile.index', $this->service->formData(auth()->user()));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $this->service->update($request->user(), $request->validated());

        return $this->success('Profil güncellendi.', [
            'name' => $user->name,
            'avatar' => $user->avatarUrl(),
            'initials' => $user->initials(),
        ]);
    }

    public function updatePassword(ProfilePasswordRequest $request): JsonResponse
    {
        $this->service->updatePassword($request->user(), $request->validated());

        return $this->success('Şifreniz güncellendi.');
    }
}
