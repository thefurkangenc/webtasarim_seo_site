<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use App\Services\Sitemap\SitemapService;
use Illuminate\Http\Response;

/**
 * robots.txt dinamik sunulur — public/robots.txt statik dosyası YOKTUR
 * (olursa web sunucusu onu Laravel'e hiç uğramadan döndürür).
 */
class RobotsController extends Controller
{
    public function __construct(private readonly SitemapService $service) {}

    public function index(): Response
    {
        return response($this->service->robotsTxt(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
