<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingCompanyRequest;
use App\Http\Requests\Admin\Setting\SettingContactRequest;
use App\Http\Requests\Admin\Setting\SettingContentsRequest;
use App\Http\Requests\Admin\Setting\SettingCookieRequest;
use App\Http\Requests\Admin\Setting\SettingMailRequest;
use App\Http\Requests\Admin\Setting\SettingMaintenanceRequest;
use App\Http\Requests\Admin\Setting\SettingSchemaRequest;
use App\Http\Requests\Admin\Setting\SettingSeoRequest;
use App\Http\Requests\Admin\Setting\SettingTrackingRequest;
use App\Services\Setting\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SettingService $service) {}

    public function edit(string $group = 'company'): View
    {
        return view('admin.pages.setting.index', $this->service->formData($group));
    }

    public function updateCompany(SettingCompanyRequest $request): JsonResponse
    {
        $this->service->putGroup('company', $request->validated());

        return $this->success('Firma bilgileri kaydedildi.');
    }

    public function updateSeo(SettingSeoRequest $request): JsonResponse
    {
        $this->service->putGroup('seo', $request->validated());

        return $this->success('SEO ayarları kaydedildi.');
    }

    public function updateSchema(SettingSchemaRequest $request): JsonResponse
    {
        $this->service->putGroup('schema', $request->validated());

        return $this->success('Schema.org ayarları kaydedildi.');
    }

    public function updateMail(SettingMailRequest $request): JsonResponse
    {
        $this->service->updateMail($request->validated());

        return $this->success('E-posta ayarları kaydedildi.');
    }

    public function testMail(SettingMailRequest $request): JsonResponse
    {
        $this->service->testMail($request->validated());

        return $this->success('Bağlantı başarılı. SMTP sunucusuna ulaşılıyor.');
    }

    public function updateTracking(SettingTrackingRequest $request): JsonResponse
    {
        $this->service->putGroup('tracking', $request->validated());

        return $this->success('İzleme kodları kaydedildi.');
    }

    public function updateContents(SettingContentsRequest $request): JsonResponse
    {
        $this->service->putGroup('contents', $request->validated());

        return $this->success('İçerikler kaydedildi.');
    }

    public function updateContact(SettingContactRequest $request): JsonResponse
    {
        $this->service->putGroup('contact', $request->validated());

        return $this->success('İletişim formu ayarları kaydedildi.');
    }

    public function updateCookie(SettingCookieRequest $request): JsonResponse
    {
        $this->service->putGroup('cookie', $request->validated());

        return $this->success('Çerez çubuğu ayarları kaydedildi.');
    }

    public function updateMaintenance(SettingMaintenanceRequest $request): JsonResponse
    {
        $this->service->putGroup('maintenance', $request->validated());

        return $this->success('Bakım modu ayarları kaydedildi.');
    }
}
