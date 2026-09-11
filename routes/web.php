<?php

use App\Http\Controllers\About\AboutController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\IndexNow\IndexNowController;
use App\Http\Controllers\Legal\LegalController;
use App\Http\Controllers\Maintenance\MaintenanceController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Sitemap\RobotsController;
use App\Http\Controllers\Sitemap\SitemapController;
use App\Support\SchemaContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home.index', ['schemaContext' => SchemaContext::home()]);
})->name('anasayfa');

Route::get('/hakkimizda', [AboutController::class, 'index'])->name('hakkimizda');

Route::get('/hizmetler', [ServiceController::class, 'index'])->name('hizmetler');

Route::get('/hizmetler/{slug}', [ServiceController::class, 'show'])->name('hizmetler.show');
Route::get('/hizmetler/{slug}/{region}', [ServiceController::class, 'showForRegion'])->name('hizmetler.show-region');

Route::get('/blog', function () {
    return view('pages.blog.index', ['schemaContext' => SchemaContext::collection('Blog', route('blog'))]);
})->name('blog');

Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/iletisim', [ContactController::class, 'index'])->name('iletisim');
Route::post('/iletisim', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('iletisim.store');

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

Route::get('/{key}.txt', [IndexNowController::class, 'key'])
    ->where('key', '[A-Za-z0-9-]{8,128}')
    ->name('indexnow.key');
