<?php

namespace App\Http\Controllers\Admin\Sitemap;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sitemap\SitemapUpdateRequest;
use App\Services\IndexNow\IndexNowService;
use App\Services\Sitemap\SitemapService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SitemapController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SitemapService $service) {}

    /*
    | Hızlı indeksleme (IndexNow) ayrı bir sayfa değil, bu sayfanın bir
    | sekmesidir — ikisi de aynı işin (arama motoruna sitenizi bildirmek)
    | parçası, kullanıcıyı iki menü öğesi arasında gezdirmenin anlamı yok.
    */
    public function index(IndexNowService $indexNow): View
    {
        return view('admin.pages.sitemap.index', $this->service->formData() + ['indexNow' => $indexNow->formData()]);
    }

    public function update(SitemapUpdateRequest $request): JsonResponse
    {
        $this->service->update($request->validated());

        return $this->success('Ayarlar kaydedildi, site haritası kuyrukta yeniden oluşturuluyor.');
    }

    public function generate(): JsonResponse
    {
        $this->service->regenerate();

        return $this->success('Site haritası kuyruğa alındı.');
    }
}
