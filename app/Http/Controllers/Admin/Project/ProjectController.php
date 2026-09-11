<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Project\ProjectCreateRequest;
use App\Http\Requests\Admin\Project\ProjectFilterRequest;
use App\Http\Requests\Admin\Project\ProjectUpdateRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Project\Project;
use App\Services\Project\ProjectService;
use App\Services\ProjectCategory\ProjectCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ProjectService $service) {}

    public function index(ProjectCategoryService $categories): View
    {
        return view('admin.pages.project.index', [
            'categories' => $categories->options(),
            'stats' => $this->service->stats(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.project.form', $this->service->formData(null));
    }

    public function edit(Project $project): View
    {
        return view('admin.pages.project.form', $this->service->formData($project));
    }

    public function datatable(ProjectFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(ProjectCreateRequest $request): JsonResponse
    {
        $project = $this->service->create($request->validated());

        return $this->success('Proje eklendi.', [
            ...$project->toPayload(),
            'redirect' => route('admin.project.edit', $project),
        ]);
    }

    public function update(ProjectUpdateRequest $request, Project $project): JsonResponse
    {
        return $this->success('Proje güncellendi.', $this->service->update($project, $request->validated())->toPayload());
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->service->delete($project);

        return $this->success('Proje silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
