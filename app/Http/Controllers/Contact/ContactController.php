<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactSubmitRequest;
use App\Services\Contact\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ContactService $service) {}

    public function index(): View
    {
        return view('pages.contact.index', $this->service->pageData());
    }

    public function store(ContactSubmitRequest $request): JsonResponse
    {
        return $this->success($this->service->submit(
            $request->validated(),
            $request->ip(),
            $request->userAgent(),
        ));
    }
}
