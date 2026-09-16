<?php

use App\Http\Controllers\About\AboutController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\IndexNow\IndexNowController;
use App\Http\Controllers\Legal\LegalController;
use App\Http\Controllers\Maintenance\MaintenanceController;
use App\Http\Controllers\Media\PlayerController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Quote\QuoteController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Sitemap\RobotsController;
use App\Http\Controllers\Sitemap\SitemapController;
use App\Http\Controllers\Subscriber\SubscriberController;
use App\Support\SchemaContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home.index', ['schemaContext' => SchemaContext::home()]);
})->name('anasayfa');

Route::get('/hakkimizda', [AboutController::class, 'index'])->name('hakkimizda');

Route::get('/hizmetler', [ServiceController::class, 'index'])->name('hizmetler');

Route::get('/hizmetler/{slug}', [ServiceController::class, 'show'])->name('hizmetler.show');
// Bölge adresi iç içedir: /hizmetler/web-tasarim/gaziantep/sahinbey. Parametre
// birden çok segment tuttuğu için kısıt elle verilir, yoksa {region} tek
// segmentte kalır ve alt bölge sayfaları hiç eşleşmez.
Route::get('/hizmetler/{slug}/{region}', [ServiceController::class, 'showForRegion'])
    ->where('region', '[a-z0-9\-]+(?:/[a-z0-9\-]+)*')
    ->name('hizmetler.show-region');

Route::get('/blog', function () {
    return view('pages.blog.index', ['schemaContext' => SchemaContext::collection('Blog', route('blog'))]);
})->name('blog');

Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

/*
| Projeler (Neler Yaptık). Kategori route'u okunabilirlik için üstte; {slug}
| tek segment eşlediği için zaten çakışmazlar. Üçü de Modül Yönetimi'ndeki
| "project" anahtarına bağlı: modül pasifse ziyaretçiye 404 döner.
*/
Route::middleware('module.active:project,404')->group(function () {
    Route::get('/projeler', [ProjectController::class, 'index'])->name('projeler');
    Route::get('/projeler/kategori/{slug}', [ProjectController::class, 'category'])->name('projeler.kategori');
    Route::get('/projeler/{slug}', [ProjectController::class, 'show'])->name('projeler.show');
});

Route::get('/iletisim', [ContactController::class, 'index'])->name('iletisim');
Route::post('/iletisim', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('iletisim.store');

Route::post('/teklif', [QuoteController::class, 'store'])
    ->middleware('throttle:quote')
    ->name('teklif.store');

Route::post('/bulten', [SubscriberController::class, 'store'])
    ->middleware('throttle:newsletter')
    ->name('bulten.store');
Route::get('/bulten/ayril/{token}', [SubscriberController::class, 'unsubscribe'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->name('bulten.unsubscribe');

Route::get('/cerez-politikasi', [LegalController::class, 'cookie'])->name('cerez-politikasi');
Route::get('/kvkk', [LegalController::class, 'kvkk'])->name('kvkk');

Route::get('/bakim-onizleme', [MaintenanceController::class, 'preview'])
    ->middleware('auth')
    ->name('maintenance.preview');
Route::get('/bakim-onizleme/{secret}', [MaintenanceController::class, 'bypass'])
    ->name('maintenance.bypass');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-{name}.xml', [SitemapController::class, 'file'])
    ->where('name', '[a-z0-9-]+')
    ->name('sitemap.file');

Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

Route::get('/media/{media}/player', [PlayerController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('media.player');

Route::get('/{key}.txt', [IndexNowController::class, 'key'])
    ->where('key', '[A-Za-z0-9-]{8,128}')
    ->name('indexnow.key');
