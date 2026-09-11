<?php

namespace App\Http\Controllers\Admin\BrokenLink;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrokenLink\BrokenLinkFilterRequest;
use App\Models\BrokenLink\BrokenLink;
use App\Services\BrokenLink\BrokenLinkService;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrokenLinkController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly BrokenLinkService $service) {}

    public function index(): View
    {
        return view('admin.pages.broken-link.index', $this->service->indexData());
    }

    public function datatable(BrokenLinkFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    /** Özet kartların bir işlem sonrası tazelenmesi için. */
    public function stats(): JsonResponse
    {
        return $this->success(data: $this->service->stats());
    }

    public function scan(): JsonResponse
    {
        $this->service->queueScan();

        return $this->success('Tarama kuyruğa alındı. Bitince liste güncellenecek.');
    }

    public function ignore(BrokenLink $brokenLink): JsonResponse
    {
        return $this->success(
            $this->service->toggleIgnore($brokenLink)->ignored ? 'Bağlantı yok sayıldı.' : 'Bağlantı yeniden denetlenecek.',
            $brokenLink->toPayload(),
        );
    }

    public function destroy(BrokenLink $brokenLink): JsonResponse
    {
        $this->service->delete($brokenLink);

        return $this->success('Kayıt silindi.');
    }

    public function export(): StreamedResponse
    {
        return Csv::download('kirik-linkler-'.now()->format('Y-m-d').'.csv', $this->service->exportRows());
    }
}
