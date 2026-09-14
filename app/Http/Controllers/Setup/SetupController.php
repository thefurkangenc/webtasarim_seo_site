<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\SetupAdminRequest;
use App\Http\Requests\Setup\SetupCompanyRequest;
use App\Http\Requests\Setup\SetupContactRequest;
use App\Http\Requests\Setup\SetupLegalRequest;
use App\Http\Requests\Setup\SetupLogoRequest;
use App\Http\Requests\Setup\SetupMailRequest;
use App\Http\Requests\Setup\SetupModulesRequest;
use App\Http\Requests\Setup\SetupRunRequest;
use App\Services\Setup\SetupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetupController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SetupService $service) {}

    public function index(Request $request): View
    {
        $payload = $this->safePayload($request);

        return view('setup.index', [
            ...$this->service->formData($payload),
            'payload' => $payload,
        ]);
    }

    public function saveAdmin(SetupAdminRequest $request): JsonResponse
    {
        $this->merge($request, 'admin', $request->validated());

        return $this->success('Yönetici bilgileri kaydedildi.');
    }

    public function saveCompany(SetupCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['logo_media_id'] = $data['logo_media_id'] ?? $request->session()->get('setup.company.logo_media_id');
        $this->merge($request, 'company', $data);

        return $this->success('Firma bilgileri kaydedildi.');
    }

    public function uploadLogo(SetupLogoRequest $request): JsonResponse
    {
        $id = $this->service->storeLogo($request->file('logo'));
        $company = $request->session()->get('setup.company', []);
        $company['logo_media_id'] = $id;
        $this->merge($request, 'company', $company);

        return $this->success('Logo yüklendi.', ['id' => $id]);
    }

    public function saveModules(SetupModulesRequest $request): JsonResponse
    {
        $this->merge($request, 'modules', $request->validated('modules'));

        return $this->success('Modüller kaydedildi.');
    }

    public function saveContact(SetupContactRequest $request): JsonResponse
    {
        $this->merge($request, 'contact', $request->validated());

        return $this->success('İletişim ayarları kaydedildi.');
    }

    public function saveMail(SetupMailRequest $request): JsonResponse
    {
        $this->merge($request, 'mail', $request->validated());

        return $this->success('E-posta ayarları kaydedildi.');
    }

    public function saveLegal(SetupLegalRequest $request): JsonResponse
    {
        $this->merge($request, 'legal', $request->validated());

        return $this->success('Yasal adım kaydedildi.');
    }

    public function run(SetupRunRequest $request): JsonResponse
    {
        $task = $request->validated('task');
        $result = $this->service->runTask($task, $request->session()->get('setup', []));

        return $this->success($result['message'] ?? 'Tamam.', $this->taskData($request, $task, $result));
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function taskData(Request $request, string $task, array $result): array
    {
        if ($task === 'finalize') {
            $result = $this->finish($request, $result);
        }

        $warning = (bool) ($result['warning'] ?? false);
        unset($result['warning']);

        return [...$result, 'warning' => $warning];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function finish(Request $request, array $result): array
    {
        $userId = $result['user_id'] ?? null;

        if ($userId) {
            Auth::login($this->service->userById((int) $userId));
            $request->session()->regenerate();
        }

        $request->session()->forget('setup');
        unset($result['user_id']);
        $result['redirect'] = route('admin.dashboard');

        return $result;
    }

    /** @param  array<string, mixed>  $data */
    private function merge(Request $request, string $key, array $data): void
    {
        $payload = $request->session()->get('setup', []);
        $payload[$key] = $data;
        $request->session()->put('setup', $payload);
    }

    /** @return array<string, mixed> */
    private function safePayload(Request $request): array
    {
        $payload = $request->session()->get('setup', []);
        unset($payload['admin']['password'], $payload['admin']['password_confirmation'], $payload['mail']['password']);

        return $payload;
    }
}
