<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Services\Page\PageService;
use App\Support\SchemaContext;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageService $service) {}

    /**
     * Dinamik sayfa. Route `web.php`'nin en sonundaki catch-all olduğu için
     * buraya yalnızca başka hiçbir route'un karşılamadığı adresler düşer;
     * eşleşen bir sayfa yoksa 404 verilir.
     */
    public function show(string $path): View
    {
        $page = $this->service->findByPath($path);
        abort_unless($page, 404);

        return view($page->templateMeta()['view'], [
            ...$this->service->viewData($page),
            'schemaContext' => SchemaContext::page($page),
        ]);
    }
}
