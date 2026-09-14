<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Services\Project\ProjectService;
use App\Support\SchemaContext;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(): View
    {
        return view('pages.projects.index', [
            ...$this->service->listing(),
            'schemaContext' => SchemaContext::collection('Neler Yaptık', route('projeler')),
        ]);
    }

    /**
     * Kategori listesi. Kategoride yayında proje yoksa sayfa boş durumla
     * açılır — 404 verilmez, çünkü kategori gerçekten var ve panelden
     * yeniden doldurulabilir.
     */
    public function category(string $slug): View
    {
        $category = $this->service->findCategoryBySlug($slug);
        abort_unless($category, 404);

        return view('pages.projects.index', [
            ...$this->service->listing($category),
            'schemaContext' => SchemaContext::collection($category->name, route('projeler.kategori', $category->slug)),
        ]);
    }

    public function show(string $slug): View
    {
        $project = $this->service->findBySlug($slug);
        abort_unless($project, 404);

        return view('pages.projects.show', [
            'project' => $project,
            'related' => $this->service->related($project),
            'schemaContext' => SchemaContext::project($project),
        ]);
    }
}
