<?php

namespace App\Http\Controllers\IndexNow;

use App\Http\Controllers\Controller;
use App\Services\IndexNow\IndexNowService;
use Illuminate\Http\Response;

/**
 * IndexNow anahtar dosyası. Protokol, sitenin kökünde `{anahtar}.txt`
 * adresinin anahtarı döndürmesini şart koşar; dosya statik tutulmaz, ayardaki
 * anahtardan üretilir — panelden anahtar yenilenince kendiliğinden uyar.
 */
class IndexNowController extends Controller
{
    public function __construct(private readonly IndexNowService $service) {}

    public function key(string $key): Response
    {
        $content = $this->service->keyFile($key);

        abort_unless($content, 404);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
