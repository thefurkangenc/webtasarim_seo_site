<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use App\Services\Sitemap\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapService $service) {}

    public function index(): Response
    {
        return $this->xml($this->service->read('sitemap.xml'));
    }

    public function file(string $name): Response
    {
        return $this->xml($this->service->read("sitemap-{$name}.xml"));
    }

    private function xml(?string $content): Response
    {
        abort_unless($content, 404);

        return response($content, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
