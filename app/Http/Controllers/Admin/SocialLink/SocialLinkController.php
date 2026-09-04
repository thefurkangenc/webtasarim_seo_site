<?php

namespace App\Http\Controllers\Admin\SocialLink;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\SocialLink\SocialLinkCreateRequest;
use App\Http\Requests\Admin\SocialLink\SocialLinkUpdateRequest;
use App\Models\SocialLink\SocialLink;
use App\Services\SocialLink\SocialLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SocialLinkService $service) {}

    public function index(): JsonResponse
    {
        return $this->success(data: $this->service->list());
    }

    public function form(?SocialLink $social_link = null): View
    {
        return view('admin.pages.setting.modals.social-link', [
            'link' => $social_link?->load('media'),
        ]);
    }

    public function store(SocialLinkCreateRequest $request): JsonResponse
    {
        return $this->success('Sosyal medya eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(SocialLinkUpdateRequest $request, SocialLink $social_link): JsonResponse
    {
        return $this->success(
            'Sosyal medya güncellendi.',
            $this->service->update($social_link, $request->validated())->toPayload(),
        );
    }

    public function destroy(SocialLink $social_link): JsonResponse
    {
        $this->service->delete($social_link);

        return $this->success('Sosyal medya silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
