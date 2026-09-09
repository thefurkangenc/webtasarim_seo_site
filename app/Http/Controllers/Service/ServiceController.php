<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Services\Service\ServiceService;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceService $service) {}

    public function index(): View
    {
        return view('pages.services.index', [
            'services' => $this->service->active(),
        ]);
    }

    /** Bölgesiz genel (şemsiye) sayfa — yer tutucular kaldırılmış haliyle gösterilir. */
    public function show(string $slug): View
    {
        $service = $this->service->findBySlug($slug);
        abort_unless($service, 404);

        return view('pages.services.show', [
            'service' => $service,
            'region' => null,
            'rendered' => $service->renderGeneric(),
        ]);
    }

    /**
     * Bölgeli sayfa — yer tutucular bu bölgenin değerleriyle çözülür.
     * Bölge, hizmete gerçekten bağlı değilse (yanlış/uydurma URL) 404 döner.
     */
    public function showForRegion(string $slug, string $regionSlug): View
    {
        $service = $this->service->findBySlug($slug);
        abort_unless($service, 404);

        $region = $service->regions->firstWhere('slug', $regionSlug);
        abort_unless($region, 404);

        return view('pages.services.show', [
            'service' => $service,
            'region' => $region,
            'rendered' => $service->renderFor($region),
        ]);
    }
}
