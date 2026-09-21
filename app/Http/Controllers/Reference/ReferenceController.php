<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Services\Reference\ReferenceService;
use App\Support\SchemaContext;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    public function __construct(private readonly ReferenceService $service) {}

    public function index(): View
    {
        return view('pages.references.index', [
            ...$this->service->listing(),
            'schemaContext' => SchemaContext::collection('Referanslar', route('referanslar')),
        ]);
    }
}
