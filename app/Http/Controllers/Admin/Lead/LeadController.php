<?php

namespace App\Http\Controllers\Admin\Lead;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Lead\LeadBulkRequest;
use App\Http\Requests\Admin\Lead\LeadFilterRequest;
use App\Http\Requests\Admin\Lead\LeadReplyRequest;
use App\Http\Requests\Admin\Lead\LeadUpdateRequest;
use App\Models\Lead\Lead;
use App\Services\Lead\LeadService;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly LeadService $service) {}

    public function index(): View
    {
        return view('admin.pages.lead.index', $this->service->indexData());
    }

    public function datatable(LeadFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function stats(): JsonResponse
    {
        return $this->success(data: $this->service->stats());
    }

    public function show(Lead $lead): View
    {
        return view('admin.pages.lead.modals.show', $this->service->showData($lead));
    }

    public function update(LeadUpdateRequest $request, Lead $lead): JsonResponse
    {
        $this->service->update($lead, $request->validated());

        return $this->success('Talep güncellendi.');
    }

    public function reply(LeadReplyRequest $request, Lead $lead): JsonResponse
    {
        $this->service->reply($lead, $request->validated());

        return $this->success('Yanıt gönderildi.');
    }

    public function toggleRead(Lead $lead): JsonResponse
    {
        $lead = $this->service->toggleRead($lead);

        return $this->success($lead->isRead() ? 'Okundu işaretlendi.' : 'Okunmadı işaretlendi.');
    }

    public function bulk(LeadBulkRequest $request): JsonResponse
    {
        $count = $this->service->bulk(
            $request->validated('ids'),
            $request->validated('action'),
            $request->validated('status'),
        );

        return $this->success("{$count} kayıt güncellendi.");
    }

    public function export(LeadFilterRequest $request): StreamedResponse
    {
        return Csv::download(
            'talepler-'.now()->format('Y-m-d-Hi').'.csv',
            $this->service->exportRows($request->validated()),
        );
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->service->delete($lead);

        return $this->success('Talep çöp kutusuna taşındı.');
    }

    public function restore(int $lead): JsonResponse
    {
        $this->service->restore(Lead::onlyTrashed()->findOrFail($lead));

        return $this->success('Talep geri alındı.');
    }
}
