<?php

use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Legal\LegalController;
use App\Http\Controllers\Maintenance\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home.index');
})->name('anasayfa');

Route::get('/hakkimizda', function () {
    return view('pages.about.index');
})->name('hakkimizda');

Route::get('/hizmetler', function () {
    return view('pages.services.index');
})->name('hizmetler');

Route::get('/hizmetler/{id}', function ($id) {
    return view('pages.services.show');
})->name('hizmetler.show');

Route::get('/blog', function () {
    return view('pages.blog.index');
})->name('blog');

Route::get('/blog/{id}', function ($id) {
    return view('pages.blog.show');
})->name('blog.show');

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
