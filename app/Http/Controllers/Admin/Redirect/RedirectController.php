<?php

namespace App\Http\Controllers\Admin\Redirect;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Redirect\NotFoundFilterRequest;
use App\Http\Requests\Admin\Redirect\RedirectFilterRequest;
use App\Http\Requests\Admin\Redirect\RedirectImportRequest;
use App\Http\Requests\Admin\Redirect\RedirectStoreRequest;
use App\Http\Requests\Admin\Redirect\RedirectUpdateRequest;
use App\Models\Redirect\NotFoundLog;
use App\Models\Redirect\Redirect;
use App\Services\Redirect\RedirectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RedirectController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly RedirectService $service) {}

    public function index(): View
    {
        return view('admin.pages.redirect.index', ['stats' => $this->service->stats()]);
    }

    public function datatable(RedirectFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    /** Özet kartların bir işlem sonrası tazelenmesi için. */
    public function stats(): JsonResponse
    {
        return $this->success(data: $this->service->stats());
    }

    public function form(?Redirect $redirect = null): View
    {
        return view('admin.pages.redirect.modals.form', $this->service->formData($redirect));
    }

    public function store(RedirectStoreRequest $request): JsonResponse
    {
        $redirect = $this->service->create($request->validated());

        // 404 kaydından geldiyse o kaydı çözüldü işaretle.
        if ($notFoundId = $request->input('not_found_id')) {
            NotFoundLog::whereKey($notFoundId)->update(['resolved' => true]);
        }

        return $this->success('Yönlendirme eklendi.', $redirect->toPayload());
    }

    public function update(RedirectUpdateRequest $request, Redirect $redirect): JsonResponse
    {
        return $this->success('Yönlendirme güncellendi.', $this->service->update($redirect, $request->validated())->toPayload());
    }

    public function toggle(Redirect $redirect): JsonResponse
    {
        return $this->success('Durum güncellendi.', $this->service->toggle($redirect)->toPayload());
    }

    public function destroy(Redirect $redirect): JsonResponse
    {
        $this->service->delete($redirect);

        return $this->success('Yönlendirme silindi.');
    }

    /** Modal'daki canlı zincir/döngü uyarısı için. */
    public function analyze(Request $request): JsonResponse
    {
        return $this->success(data: $this->service->analyze(
            (string) $request->query('from', ''),
            $request->query('to'),
            $request->query('ignore') ? (int) $request->query('ignore') : null,
        ));
    }

    public function export(): StreamedResponse
    {
        $rows = $this->service->exportRows();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');

            // Excel'in UTF-8'i Türkçe karakterlerle doğru açması için BOM.
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                // RFC 4180: kaçış karakteri yok (PHP 8.4'te açıkça verilmeli).
                fputcsv($handle, $row, ',', '"', '');
            }

            fclose($handle);
        }, 'yonlendirmeler-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(RedirectImportRequest $request): JsonResponse
    {
        $rows = $this->readCsv($request->file('file')->getRealPath());

        if ($rows === null) {
            return $this->error('CSV başlığı beklenen sütunları içermiyor: '.implode(', ', RedirectService::CSV_HEADER));
        }

        $result = $this->service->import($rows);

        return $this->success(
            "{$result['created']} eklendi, {$result['updated']} güncellendi, {$result['skipped']} atlandı.",
            $result,
        );
    }

    /* ------------------------------------------------------------------ *
     | 404 kayıtları
     * ------------------------------------------------------------------ */

    public function notFoundDatatable(NotFoundFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->notFoundList($request->validated()));
    }

    public function destroyNotFound(NotFoundLog $notFoundLog): JsonResponse
    {
        $this->service->deleteNotFound($notFoundLog);

        return $this->success('Kayıt silindi.');
    }

    /**
     * CSV'yi satır dizisine çevirir. Başlık RedirectService::CSV_HEADER ile
     * eşleşmezse null döner.
     *
     * @return array<int, array<string, string>>|null
     */
    private function readCsv(string $path): ?array
    {
        $handle = fopen($path, 'rb');
        $header = fgetcsv($handle, 0, ',', '"', '');

        // Excel BOM'unu ilk hücreden temizle.
        if (is_array($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        if (! $header || array_diff(RedirectService::CSV_HEADER, array_map('trim', $header))) {
            fclose($handle);

            return null;
        }

        $header = array_map('trim', $header);
        $rows = [];

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($line === [null] || $line === false) {
                continue;
            }

            $rows[] = array_combine($header, array_pad(array_slice($line, 0, count($header)), count($header), ''));
        }

        fclose($handle);

        return $rows;
    }
}
