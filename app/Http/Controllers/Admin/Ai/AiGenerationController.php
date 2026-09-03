<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ai\AiGenerateRequest;
use App\Models\Ai\AiGeneration;
use App\Models\Ai\AiPrompt;
use App\Services\Ai\AiPromptService;
use App\Services\Ai\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AiGenerationController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly AiService $service,
        private readonly AiPromptService $prompts,
    ) {}

    /** Modül formundan açılan üretim modalının gövdesi. */
    public function form(string $key): View
    {
        return view('admin.pages.ai.modals.generate', [
            'promptKey' => $key,
            'prompts' => $this->prompts->forKey($key),
        ]);
    }

    public function store(AiGenerateRequest $request): JsonResponse
    {
        $prompt = AiPrompt::findOrFail($request->validated('ai_prompt_id'));

        return $this->success(
            'Üretim kuyruğa alındı.',
            $this->service->dispatch($prompt, $request->validated('input'))->toPayload(),
        );
    }

    /** Arayüzün durum sorgusu. Kullanıcı yalnızca kendi üretimini okur. */
    public function show(AiGeneration $generation): JsonResponse
    {
        abort_unless($generation->user_id === auth()->id(), 403);

        return $this->success(data: $generation->toPayload());
    }
}
