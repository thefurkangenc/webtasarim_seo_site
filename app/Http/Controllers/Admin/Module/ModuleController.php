<?php

namespace App\Http\Controllers\Admin\Module;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Module\ModuleUpdateRequest;
use App\Services\Module\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ModuleController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ModuleService $service) {}

    public function index(): View
    {
        return view('admin.pages.module.index', $this->service->formData());
    }

    public function update(ModuleUpdateRequest $request): JsonResponse
    {
        $this->service->update($request->validated());

        return $this->success('Modül ayarları güncellendi.');
    }
}
