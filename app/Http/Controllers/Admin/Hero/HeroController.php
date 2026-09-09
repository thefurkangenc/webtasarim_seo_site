<?php

namespace App\Http\Controllers\Admin\Hero;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hero\HeroUpdateRequest;
use App\Services\Hero\HeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HeroController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly HeroService $service) {}

    public function edit(): View
    {
        return view('admin.pages.hero.index', ['hero' => $this->service->current()]);
    }

    public function update(HeroUpdateRequest $request): JsonResponse
    {
        $this->service->update($request->validated());

        return $this->success('Tanıtım alanı güncellendi.');
    }
}
