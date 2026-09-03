<?php

namespace App\Http\Controllers\Admin\AiPrompt;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiPrompt\AiPromptFilterRequest;
use App\Http\Requests\Admin\AiPrompt\AiPromptRequest;
use App\Models\Ai\AiProvider;
use App\Models\Ai\AiPrompt;
use App\Services\Ai\AiPromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AiPromptController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly AiPromptService $service) {}

    public function index(): View
    {
        return view('admin.pages.ai-prompt.index');
    }

    public function form(?AiPrompt $prompt = null): View
    {
        return view('admin.pages.ai-prompt.modals.form', [
            'prompt' => $prompt,
            'providers' => AiProvider::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function datatable(AiPromptFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function store(AiPromptRequest $request): JsonResponse
    {
        return $this->success('Şablon eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(AiPromptRequest $request, AiPrompt $prompt): JsonResponse
    {
        return $this->success(
            'Şablon güncellendi.',
            $this->service->update($prompt, $request->validated())->toPayload(),
        );
    }

    public function destroy(AiPrompt $prompt): JsonResponse
    {
        $this->service->delete($prompt);

        return $this->success('Şablon silindi.');
    }
}
