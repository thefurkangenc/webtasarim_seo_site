<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Services\Project\ProjectService;
use App\Services\Quote\QuoteService;
use App\Services\Service\ServiceService;
use App\Support\SchemaContext;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $service,
        private readonly ProjectService $projects,
        private readonly QuoteService $quotes,
    ) {}

    public function index(): View
    {
        return view('pages.services.index', [
            'services' => $this->service->active(),
            'schemaContext' => SchemaContext::collection('Hizmetler', route('hizmetler')),
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
            'regionGroups' => $this->service->regionGroups($service),
            'projects' => $this->projects->active(6, null, $service->id),
            'otherServices' => $this->service->active()->where('id', '!=', $service->id)->values(),
            'quoteServices' => $this->quotes->serviceOptions(),
            'schemaContext' => SchemaContext::service($service),
        ]);
    }

    /**
     * Bölgeli sayfa — yer tutucular bu bölgenin değerleriyle çözülür. Adres
     * iç içedir ({şehir}/{ilçe}); hizmete bağlı olmayan bir yol 404 döner.
     */
    public function showForRegion(string $slug, string $region): View
    {
        $service = $this->service->findBySlug($slug);
        abort_unless($service, 404);

        $serviceRegion = $this->service->findRegion($service, $region);
        abort_unless($serviceRegion, 404);

        return view('pages.services.show', [
            'service' => $service,
            'region' => $serviceRegion,
            'rendered' => $service->renderFor($serviceRegion),
            'regionGroups' => $this->service->regionGroups($service),
            'projects' => $this->projects->active(6, null, $service->id),
            'otherServices' => $this->service->active()->where('id', '!=', $service->id)->values(),
            'quoteServices' => $this->quotes->serviceOptions(),
            'schemaContext' => SchemaContext::service($service, $serviceRegion),
        ]);
    }
}
