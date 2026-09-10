<?php

use App\Http\Controllers\About\AboutController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Legal\LegalController;
use App\Http\Controllers\Maintenance\MaintenanceController;
use App\Http\Controllers\Service\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home.index');
})->name('anasayfa');

Route::get('/hakkimizda', [AboutController::class, 'index'])->name('hakkimizda');

Route::get('/hizmetler', [ServiceController::class, 'index'])->name('hizmetler');

// Bölgesiz (şemsiye) sayfa ve bölgeli sayfa segment sayısı farklı olduğu
// için çakışmaz — Laravel URI'yi segment sayısına göre eşleştirir.
Route::get('/hizmetler/{slug}', [ServiceController::class, 'show'])->name('hizmetler.show');
Route::get('/hizmetler/{slug}/{region}', [ServiceController::class, 'showForRegion'])->name('hizmetler.show-region');

Route::get('/blog', function () {
    return view('pages.blog.index');
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

/*
| Panelden yönetilen dinamik sayfaların catch-all route'u bu dosyada DEĞİL,
| `routes/pages.php` içindedir — tüm uygulamada en son kaydolması gerekiyor ve
| bu dosya `bootstrap/app.php` içinde admin route'larından önce yükleniyor.
| Ayrıntı orada.
*/
