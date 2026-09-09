<?php

namespace App\Http\Controllers\Admin\Testimonial;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\Testimonial\TestimonialCreateRequest;
use App\Http\Requests\Admin\Testimonial\TestimonialFilterRequest;
use App\Http\Requests\Admin\Testimonial\TestimonialUpdateRequest;
use App\Models\Testimonial\Testimonial;
use App\Services\Testimonial\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly TestimonialService $service) {}

    public function index(): View
    {
        return view('admin.pages.testimonial.index');
    }

    public function datatable(TestimonialFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Testimonial $testimonial = null): View
    {
        return view('admin.pages.testimonial.modals.form', $this->service->formData($testimonial?->load('media')));
    }

    public function store(TestimonialCreateRequest $request): JsonResponse
    {
        return $this->success('Müşteri yorumu eklendi.', $this->service->create($request->validated())->toPayload());
    }

    public function update(TestimonialUpdateRequest $request, Testimonial $testimonial): JsonResponse
    {
        return $this->success(
            'Müşteri yorumu güncellendi.',
            $this->service->update($testimonial, $request->validated())->toPayload(),
        );
    }

    public function destroy(Testimonial $testimonial): JsonResponse
    {
        $this->service->delete($testimonial);

        return $this->success('Müşteri yorumu silindi.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'));

        return $this->success('Sıralama güncellendi.');
    }
}
