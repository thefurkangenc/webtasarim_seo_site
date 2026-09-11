<?php

namespace App\Http\Controllers\Admin\Subscriber;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subscriber\SubscriberFilterRequest;
use App\Models\Subscriber\Subscriber;
use App\Services\Subscriber\SubscriberService;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SubscriberService $service) {}

    public function index(): View
    {
        return view('admin.pages.subscriber.index', $this->service->indexData());
    }

    public function datatable(SubscriberFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function destroy(Subscriber $subscriber): JsonResponse
    {
        $this->service->delete($subscriber);

        return $this->success('Abone silindi.');
    }

    public function export(): StreamedResponse
    {
        return Csv::download('bulten-aboneleri-'.now()->format('Y-m-d').'.csv', $this->service->exportRows());
    }
}
